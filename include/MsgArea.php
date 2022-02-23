<?php
include_once("DB.php");
include_once("MsgBase.php");

class MsgAreaException extends MsgBaseException{};

class MsgArea
{
    //**  Message Attribute bits
    public const ATTRIB_LOCAL      = 0x00000001; /* Msg created locally */
    public const ATTRIB_INTRANSIT  = 0x00000002; /* Msg is in-transit */
    public const ATTRIB_PRIVATE    = 0x00000004; /* Private */
    public const ATTRIB_READ       = 0x00000008; /* Read by addressee */
    public const ATTRIB_SENT       = 0x00000010; /* Sent to remote */
    public const ATTRIB_KILLSENT   = 0x00000020; /* Kill when sent */
    public const ATTRIB_ARCHIVESENT= 0x00000040; /* Archive when sent */
    public const ATTRIB_HOLD       = 0x00000080; /* Hold for pick-up */
    public const ATTRIB_CRASH      = 0x00000100; /* Crash */
    public const ATTRIB_IMMEDIATE  = 0x00000200; /* Send Msg now, ignore restrictions */
    public const ATTRIB_DIRECT     = 0x00000400; /* Send directly to destination */
    public const ATTRIB_GATE       = 0x00000800; /* Send via gateway */
    public const ATTRIB_FILEREQUEST= 0x00001000; /* File request */
    public const ATTRIB_FILEATTACH = 0x00002000; /* File(s) attached to Msg */
    public const ATTRIB_TRUNCFILE  = 0x00004000; /* Truncate file(s) when sent */
    public const ATTRIB_KILLFILE   = 0x00008000; /* Delete file(s) when sent */
    public const ATTRIB_RECEIPTREQ = 0x00010000; /* Return receipt requested */
    public const ATTRIB_CONFIRMREQ = 0x00020000; /* Confirmation receipt requested */
    public const ATTRIB_ORPHAN     = 0x00040000; /* Unknown destination */
    public const ATTRIB_ENCRYPT    = 0x00080000; /* Msg text is encrypted */
    public const ATTRIB_COMPRESS   = 0x00100000; /* Msg text is compressed */
    public const ATTRIB_ESCAPED    = 0x00200000; /* Msg text is seven bit ASCII */
    public const ATTRIB_FPU        = 0x00400000; /* Force pickup */
    public const ATTRIB_TYPELOCAL  = 0x00800000; /* Msg is for local use only (not for export) */
    public const ATTRIB_TYPEECHO   = 0x01000000; /* Msg is for conference distribution */
    public const ATTRIB_TYPENET    = 0x02000000; /* Msg is direct network mail */
    public const ATTRIB_NODISP     = 0x20000000; /* Msg may not be displayed to user */
    public const ATTRIB_LOCKED     = 0x40000000; /* Msg is locked, no editing possible */
    public const ATTRIB_DELETED    = 0x80000000; /* Msg is deleted */

    public $desc, $readsec, $writesec, $sysopsec, $reqattrib, $optattrib, $type, $path;
    private $_name, $_base;

    public static function attribString(int $attrib){
        $retVal = "";
        if( ($attrib & MsgArea::ATTRIB_LOCAL) !=0 ) $retVal.="Local ";
        if( ($attrib & MsgArea::ATTRIB_INTRANSIT) != 0 ) $retVal.="InTransit ";
        if( ($attrib & MsgArea::ATTRIB_PRIVATE) != 0 ) $retVal.="Private ";
        if( ($attrib & MsgArea::ATTRIB_READ) != 0 ) $retVal.="Read ";
        if( ($attrib & MsgArea::ATTRIB_KILLSENT) != 0 ) $retVal.="Kill/Sent ";
        if( ($attrib & MsgArea::ATTRIB_ARCHIVESENT) != 0 ) $retVal.="Archive/Sent ";
        if( ($attrib & MsgArea::ATTRIB_HOLD) != 0 ) $retVal.="Hold ";
        if( ($attrib & MsgArea::ATTRIB_CRASH) != 0 ) $retVal.="Crash ";
        if( ($attrib & MsgArea::ATTRIB_IMMEDIATE) != 0 ) $retVal.="Immediate ";
        if( ($attrib & MsgArea::ATTRIB_DIRECT) != 0 ) $retVal.="Direct ";
        if( ($attrib & MsgArea::ATTRIB_GATE) != 0 ) $retVal.="Gate ";
        if( ($attrib & MsgArea::ATTRIB_FILEREQUEST) != 0 ) $retVal.="FileRequest ";
        if( ($attrib & MsgArea::ATTRIB_FILEATTACH) != 0 ) $retVal.="FileAttach ";
        if( ($attrib & MsgArea::ATTRIB_TRUNCFILE) != 0 ) $retVal.="TruncFile ";
        if( ($attrib & MsgArea::ATTRIB_KILLFILE) != 0 ) $retVal.="KillFile ";
        if( ($attrib & MsgArea::ATTRIB_RECEIPTREQ) != 0 ) $retVal.="RecptReq ";
        if( ($attrib & MsgArea::ATTRIB_CONFIRMREQ) != 0 ) $retVal.="ConfirmReq ";
        if( ($attrib & MsgArea::ATTRIB_ORPHAN) != 0 ) $retVal.="Orphan ";
        if( ($attrib & MsgArea::ATTRIB_ENCRYPT) != 0 ) $retVal.="Encrypt ";
        if( ($attrib & MsgArea::ATTRIB_ESCAPED) != 0 ) $retVal.="Escaped ";
        if( ($attrib & MsgArea::ATTRIB_FPU) != 0 ) $retVal.="ForcePickup ";
        if( ($attrib & MsgArea::ATTRIB_TYPELOCAL) != 0 ) $retVal.="LocalMsgbase ";
        if( ($attrib & MsgArea::ATTRIB_TYPEECHO) != 0 ) $retVal.="EchoMsgbase ";
        if( ($attrib & MsgArea::ATTRIB_TYPENET) != 0 ) $retVal.="NetMsgbase ";
        if( ($attrib & MsgArea::ATTRIB_NODISP) != 0 ) $retVal.="NoDisplay ";
        if( ($attrib & MsgArea::ATTRIB_LOCKED) != 0 ) $retVal.="Locked ";
        if( ($attrib & MsgArea::ATTRIB_DELETED) != 0 ) $retVal.="Deleted ";

        return trim($retVal);
    }
    
    public static function getList(): array
    {
        $stmt = DB_Factory::getDatabase()->query("SELECT name, readsec FROM msgareas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getListBySecurity(int $readsec): array
    {
        $pstmt = DB_Factory::getDatabase()->prepare("SELECT * FROM msgareas WHERE readsec<=:1 ORDER BY orderid,type");
        if ($pstmt->execute(array(":1" => $readsec))) {
            $retVal = array();
            foreach ($pstmt->fetchAll(PDO::FETCH_ASSOC) as $dbrec) {
                $retVal[] = new MsgArea($dbrec);
            }
            return $retVal;
        } else {
            throw new MsgAreaException("Unable to execute query of Message Areas");
        }
    }
    public static function getByName(string $areaname): MsgArea
    {
        $pstmt = DB_Factory::getDatabase()->prepare("SELECT * FROM msgareas WHERE name=:1");
        if ($pstmt->execute(array(":1" => $areaname))) {
            if($pstmt->rowCount()==1) {
                return new MsgArea($pstmt->fetch(PDO::FETCH_ASSOC));
            } else {
                throw new MsgAreaException("Unknown area ".$areaname);
            }
        } else {
            throw new MsgAreaException("Unable to execute query of Message Areas");
        }
    }

    private function __construct($dbRecord)
    {
        $this->_name = $dbRecord['name'];
        $this->desc = $dbRecord['descr'];
        $this->readsec = $dbRecord['readsec'];
        $this->writesec = $dbRecord['writesec'];
        $this->sysopsec = $dbRecord['sysopsec'];
        $this->reqattrib = $dbRecord['reqattrib'];
        $this->optattrib = $dbRecord['optattrib'];
        $this->type = $dbRecord['type'];
        $this->path = $dbRecord['path'];

        switch ($this->type) {
            case 2:    //ECHOMAIL
                $pstmt = DB_Factory::getDatabase()->prepare("SELECT origin from origins WHERE id=:1");
                $pstmt->execute(array(":1" => $dbRecord['originid']));
                $this->origin = $pstmt->fetch(PDO::FETCH_ASSOC)['origin'];

            case 1:    //NETMAIL  (and yes, fallthrough here is intentional as ECHO and NET MAIL need AKA defined)
                $pstmt = DB_Factory::getDatabase()->prepare("SELECT aka from akas WHERE id=:1");
                $pstmt->execute(array(":1" => $dbRecord['akaid']));
                $this->aka = $pstmt->fetch(PDO::FETCH_ASSOC)['aka'];
                break;

            case 0:    //LOCAL
                break;

            default:
                throw new MsgAreaException("Unknown Areatype: " . $this->type);
        }
    }

    public function __get(string $name): mixed
    {
        switch ($name) {
            case 'name':
                return $this->_name;

            case 'base':
                if (!$this->_base) {
                    $this->_base = new MsgBase($this->path, "UTF-8");
                }
                return $this->_base;
        }
    }
}
