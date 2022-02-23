<?php
require_once("Fido.php");

class MsgBaseException extends RuntimeException
{
};

// The JAM style message base [see SMAPI source for details on the API used here]
class MsgBase
{

    private $jhr, $jdt, $jdx, $jlr;
    private $fh_jhr, $fh_jdt, $fh_jdx;
    private static $SFType = array(
        0 => 'OriginAddress', 1 => 'DestAddress', 2 => 'SenderName', 3 => 'ReceiverName', 4 => 'MSGID', 5 => 'REPLYID',
        6 => 'Subject', 7 => 'PID', 8 => 'TRACE', 9 => 'EnclosedFile', 10 => 'EnclosedFileWAlias', 11 => 'EnclosedFREQ',
        12 => 'EnclosedFileWildCard', 13 => 'EnclosedIndirectFile',
        1000 => 'EmbinDat', 2000 => 'FTSKludge', 2001 => 'SEENBY2D', 2002 => 'PATH2D', 2003 => 'FLAGS', 2004 => 'TZUTCINFO'
    );

    public function __construct(string $path, string $chrset = "IBM-PC")
    {
        $this->jhr = $path . ".jhr";
        $this->jdt = $path . ".jdt";
        $this->jdx = $path . ".jdx";
        $this->jlr = $path . ".jlr";

        if (file_exists($this->jhr) && file_exists($this->jdt) && file_exists($this->jdx)) {
            //Should we check signatures here?
        } else {
            throw new MsgBaseException("Not all required JAM files exist as " . $path);
        }
    }

    private function openJAM(): void
    {
        if (!$this->fh_jhr) $this->fh_jhr = fopen($this->jhr, "rb+");
        if (!$this->fh_jdx) $this->fh_jdx = fopen($this->jdx, "rb+");
        if (!$this->fh_jdt) $this->fh_jdt = fopen($this->jdt, "rb+");
        if (!$this->fh_jhr || !$this->fh_jdx || !$this->fh_jdt) {
            throw new MsgBaseException("Unable to open critical JAM files : " . error_get_last()['message']);
        }
        // fseek($this->fh_jhr, 1024 ); // JHR has 1K of header 'stuff', we probably SHOULD care about it.
    }

    public function getMessageCount(): int
    {

        $this->openJAM();
        fseek($this->fh_jhr, 0);
        $jhrHdrRec = fread($this->fh_jhr, 1024);
        if (strlen($jhrHdrRec) != 1024) {
            throw new MsgBaseException("Unable to read JHR header record");
        }
        if (strncmp($jhrHdrRec, "JAM\x00", 4) != 0) {
            throw new MsgBaseException("Unable to validate JAM signature in JHR header record");
        }

        return Fido::dword($jhrHdrRec, 12); // JHR.ActiveMsgs
    }

    public function getHeaders(): array
    {
        $retVal = array();
        $msgnum = 0;

        $this->openJAM();
        fseek($this->fh_jdx, 0);
        while (strlen($jdxRec = fread($this->fh_jdx, 8)) == 8) {
            ++$msgnum;
            // $UserCRC = Fido::dword( $jdxRec, 0);
            $HdrOffset = Fido::dword($jdxRec, 4);
            if ($HdrOffset == 4294967295) {
                // Message marked deleted
                continue;
            }
            if ($HdrOffset >= fstat($this->fh_jhr)['size']) {
                error_log("JamMsgBase: $this->jdx @ JDX->JHR [" .
                    ord($jdxRec[4]) . " " . ord($jdxRec[5]) . " " . ord($jdxRec[6]) . " " . ord($jdxRec[7]), 4);

                error_log("JamMsgBase: $this->jdx @ jhrOffset out of bound :: jhrOffset = " . $HdrOffset, 4);
                break;
            }
            // JHR
            fseek($this->fh_jhr, $HdrOffset);
            $HdrRec = fread($this->fh_jhr, 76);

            // Validate Header with Signature
            if (substr($HdrRec, 0, 4) != "JAM\x00") {
                error_log("JamMsgBase: An invalid signature in getHeaders()[" .
                    ord($HdrRec[0]) . " " . ord($HdrRec[1]) .
                    ord($HdrRec[2]) . " " . ord($HdrRec[3]) . "]", 4);
                throw new MsgBaseException("Invalid Signature in Header");
            }
            if (Fido::dword($HdrRec, 64) > 0) {
                $hdr = array();
                $hdr['num'] = Fido::dword($HdrRec, 48);
                //$hdr['filename'] = "JAM[". $this->jhr . "@".$HdrOffset."]";
                $hdr['replyTo'] = Fido::dword($HdrRec, 24);
                $hdr['firstReply'] = Fido::dword($HdrRec, 28);
                $hdr['nextReply'] = Fido::dword($HdrRec, 32);
                $hdr['date'] = Fido::timeToFido(Fido::dword($HdrRec, 36));
                $hdr['attrib'] = Fido::dword($HdrRec, 52);
                $hdr['received'] = Fido::timeToFido(Fido::dword($HdrRec, 40));
                $hdr['modified'] = Fido::timeToFido(Fido::dword($HdrRec, 44));

                $SFRec = fread($this->fh_jhr, Fido::dword($HdrRec, 8));
                $SFOffset = 0;

                while ($SFOffset < strlen($SFRec)) {
                    $sfd = mb_convert_encoding(trim(substr($SFRec, $SFOffset + 8, Fido::word($SFRec, $SFOffset + 4))), "UTF-8", null);
                    switch (Fido::word($SFRec, $SFOffset)) {
                        case 0:
                            $hdr['origAddr'] = $sfd;
                            break;
                        case 1:
                            $hdr['destAddr'] = $sfd;
                            break;
                        case 2:
                            $hdr['fromName'] = $sfd;
                            break;
                        case 3:
                            $hdr['toName'] = $sfd;
                            break;
                        case 6:
                            $hdr['subject'] = $sfd;
                            break;


                        default: /* ignore */
                            //error_log("DEBUG: Unhandled subfield type: ".JamMsgBase::$SFType[Fido::word($SFRec, $SFOffset)]." Data:[".$sfd."]",4);
                            break;
                    }

                    $SFOffset += Fido::word($SFRec, $SFOffset + 4) + 8;
                }
                $retVal[] = $hdr;
            } else {
                // Message has no text
                echo "<h6>Message has no text</h6>";
                return false;
            }
        }
        return $retVal;
    }

    /**
     * Gets a single header from JAMBase
     * @param int $msgnum Message number of Header to retrieve
     * @throws MsgBaseException
     * @return array|false array for successful read | false for deleted message
     */
    public function getHeader(int $msgnum): array | false
    {
        if ($msgnum < 1) {
            throw new MsgBaseException("Invalid message number requested: 0");
        }
        $this->openJAM();
        $jdxstat = fstat($this->fh_jdx);
        $jdxOffset = ($msgnum - 1) * 8;
        if ($jdxOffset >= $jdxstat['size']) {
            throw new MsgBaseException("Invalid message number requested: " . $msgnum . " only " . $jdxstat['size'] / 8 . " messages in base");
        }

        fseek($this->fh_jdx, $jdxOffset);
        if ($jdxRec = fread($this->fh_jdx, 8)) {
            $hdrOffset = Fido::dword($jdxRec, 4);
            if ($hdrOffset == 4294967295) {
                // Message marked deleted
                return false;
            }
            fseek($this->fh_jhr, $hdrOffset);
            if (!$HdrRec = fread($this->fh_jhr, 76)) {
                error_log("JamMsgBase: An invalid signature in getHeader()");
                throw new MsgBaseException("Unable to read message header information");
            }

            // Validate Header with Signature
            if (substr($HdrRec, 0, 4) != "JAM\x00") {
                error_log("JamMsgBase: An invalid signature in getHeaders()");
                throw new MsgBaseException("Invalid Signature in Header");
            }
            if (Fido::dword($HdrRec, 64) > 0) {
                $hdr = array();
                $hdr['num'] = Fido::dword($HdrRec, 48);
                //$hdr['filename'] = "JAM[". $this->jhr . "@".Fido::dword( $jdxRec, 4 )."]";
                $hdr['replyTo'] = Fido::dword($HdrRec, 24);
                $hdr['firstReply'] = Fido::dword($HdrRec, 28);
                $hdr['nextReply'] = Fido::dword($HdrRec, 32);
                echo "<pre>date::[" . ord($HdrRec[36]) . "][" . ord($HdrRec[37]) . "][" . ord($HdrRec[38]) . "][" . ord($HdrRec[39]) . "]</pre>";
                $hdr['date'] = Fido::timeToFido(Fido::dword($HdrRec, 36));
                $hdr['attrib'] = Fido::dword($HdrRec, 52);
                $hdr['received'] = Fido::timeToFido(Fido::dword($HdrRec, 40));
                $hdr['modified'] = Fido::timeToFido(Fido::dword($HdrRec, 44));

                $SFRec = fread($this->fh_jhr, Fido::dword($HdrRec, 8));
                $SFOffset = 0;

                while ($SFOffset < strlen($SFRec)) {
                    $sfd = mb_convert_encoding(trim(substr($SFRec, $SFOffset + 8, Fido::word($SFRec, $SFOffset + 4))), "UTF-8", null);
                    switch (Fido::word($SFRec, $SFOffset)) {
                        case 0:
                            $hdr['origAddr'] = $sfd;
                            break;
                        case 1:
                            $hdr['destAddr'] = $sfd;
                            break;
                        case 2:
                            $hdr['fromName'] = $sfd;
                            break;
                        case 3:
                            $hdr['toName'] = $sfd;
                            break;
                        case 6:
                            $hdr['subject'] = $sfd;
                            break;


                        default: /* ignore */
                            //error_log("DEBUG: Unhandled subfield type: ".JamMsgBase::$SFType[Fido::word($SFRec, $SFOffset)]." Data:[".$sfd."]",4);
                            break;
                    }

                    $SFOffset += Fido::word($SFRec, $SFOffset + 4) + 8;
                }
                return $hdr;
            } else {
                // Message has no text - mark of deleted message
                return false;
            }
        } else {
            throw new MsgBaseException("Unable to read JDX record");
        }
    }

    public function getMessage(int $msgnum): array | false
    {
        if ($msgnum < 1) {
            throw new MsgBaseException("Invalid message number requested: 0");
        }
        $this->openJAM();
        $jdxstat = fstat($this->fh_jdx);

        $jdxOffset = ($msgnum - 1) * 8;
        if ($jdxOffset >= $jdxstat['size']) {
            throw new MsgBaseException("Invalid message number requested: " . $msgnum . ", only " . $jdxstat['size'] / 8 . " messages in base");
        }

        fseek($this->fh_jdx, $jdxOffset);
        if ($jdxRec = fread($this->fh_jdx, 8)) {
            $hdrOffset = Fido::dword($jdxRec, 4);
            if ($hdrOffset == 4294967295) {
                // Message marked deleted
                return false;
            }
            fseek($this->fh_jhr, $hdrOffset);
            $HdrRec = fread($this->fh_jhr, 76);

            // Validate Header with Signature
            if (substr($HdrRec, 0, 4) != "JAM\x00") {
                error_log("JamMsgBase: An invalid signature in getHeader()");
                throw new MsgBaseException("Invalid Signature in Header");
            }

            if (Fido::dword($HdrRec, 64) == 0) {
                // Message Deleted skip further processing
                echo "<h6>Message has 0 text</h6>";
                return false;
            } else {
                $msg_encoding = "CP850";

                $retVal = array();
                $retVal['num'] = Fido::dword($HdrRec, 48);
                //$retVal['filename'] = "JAM[". $this->jhr . " @ ".Fido::dword( $jdxRec, 4 )."]";
                $retVal['replyTo'] = Fido::dword($HdrRec, 24);
                $retVal['firstReply'] = Fido::dword($HdrRec, 28);
                $retVal['nextReply'] = Fido::dword($HdrRec, 32);
                $retVal['date'] = Fido::timeToFido(Fido::dword($HdrRec, 36));
                $retVal['attrib'] = Fido::dword($HdrRec, 52);
                $retVal['received'] = Fido::timeToFido(Fido::dword($HdrRec, 40));
                $retVal['modified'] = Fido::timeToFido(Fido::dword($HdrRec, 44));

                $retVal['body'] = array();

                $SFRec = fread($this->fh_jhr, Fido::dword($HdrRec, 8));
                $SFOffset = 0;

                while ($SFOffset < strlen($SFRec)) {
                    $sfd = mb_convert_encoding(trim(substr($SFRec, $SFOffset + 8, Fido::word($SFRec, $SFOffset + 4))), "UTF-8", null);
                    switch (Fido::word($SFRec, $SFOffset)) {
                        case 0:
                            $retVal['origAddr'] = $sfd;
                            break;
                        case 1:
                            $retVal['destAddr'] = $sfd;
                            break;
                        case 2:
                            $retVal['fromName'] = $sfd;
                            break;
                        case 3:
                            $retVal['toName'] = $sfd;
                            break;
                        case 6:
                            $retVal['subject'] = $sfd;
                            break;

                        case 4:
                            $retVal['body'][] = array(true, "MSGID: " . $sfd);
                            break;
                        case 5:
                            $retVal['body'][] = array(true, "REPLYID: " . $sfd);
                            break;
                        case 7:
                            $retVal['body'][] = array(true, "PID: " . $sfd);
                            break;
                        case 8:
                            $retVal['body'][] = array(true, "VIA: " . $sfd);
                            break;
                        case 2000:
                            $retVal['body'][] = array(true, $sfd);
                            if (strncmp("CHRS:", $sfd, 5) == 0) {
                                $cse = explode(" ", trim(substr($sfd, 5)));
                                if (count($cse) != 2) {
                                    error_log("Message has unknown CHRS: format:[" . $sfd . "]", 4);
                                } else {
                                    $msg_encoding = Fido::lookupEncoder($cse[0]);
                                }
                            } elseif (strncmp("CHARSET:", $sfd, 8) == 0) {
                                $chrset = trim(substr($sfd, 9));
                                $msg_encoding = Fido::lookupEncoder($chrset);
                                error_log("WARNING: Message # " . $msgnum . " @ " . $this->jhr . " has a legacy CHRSET: kludge[" . $chrset . " => " . $msg_encoding . "]", 4);
                            }
                            break;
                        case 2001:
                            $retVal['body'][] = array(true, "SEENBY: " . $sfd);
                            break;
                        case 2002:
                            $retVal['body'][] = array(true, "PATH: " . $sfd);
                            break;
                        case 2003:
                            $retVal['body'][] = array(true, "FLAGS: " . $sfd);
                            break;
                        case 2004:
                            $retVal['body'][] = array(true, "TZUTC: " . $sfd);
                            break;

                        default: /* ignore */
                            //error_log("DEBUG: Unhandled subfield type: ".JamMsgBase::$SFType[Fido::word($SFRec, $SFOffset)]." Data:[".$sfd."]",4);
                            break;
                    }
                    $SFOffset += Fido::word($SFRec, $SFOffset + 4) + 8;
                }

                if (Fido::dword($HdrRec, 64) > 0) {
                    fseek($this->fh_jdt, Fido::dword($HdrRec, 60));
                    $JDTtext = fread($this->fh_jdt, Fido::dword($HdrRec, 64));
                    $NL = chr(10);
                    $FF = chr(6);
                    $CR = chr(13);
                    $SCR = chr(141);
                    $line = "";
                    for ($i = 0; $i < strlen($JDTtext); $i++) {
                        $char = $JDTtext[$i];
                        if ($char  == $SCR || $char == $NL) {
                            //ignore
                        } else if ($char == $CR) {
                            $retVal['body'][] = array(false, mb_convert_encoding($line, "UTF-8", $msg_encoding));
                            $line = "";
                        } else {
                            $line .= $char;
                        }
                    }
                }
                return $retVal;
            }
        } else {
            throw new MsgBaseException("Error reading header data");
        }
        return false;
    }
}
