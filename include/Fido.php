<?php
class Fido
{
    // returns integer value of a two byte, fido binary word value [platform independent, MSB vs LSB]
    public static function word( string $data, int $offset) : int
    {
        return ord( $data[$offset] ) |
               ord( $data[$offset+1]) * 256;
    }

    // sets the value into a given binary data string as a word
    public static function putWord( string $data, int $offset, int $value ) : void {
        $data[$offset] = chr( $value % 256 );
        $data[$offset+1] = chr( $value / 256 );
    }

    // returns integer value of a four byte, fido binary double word value
    public static function dword( string $data, int $offset) : int
    {
        return ord( $data[$offset] ) |
               ord( $data[$offset+1] ) * 256 |
               ord( $data[$offset+2] ) * 65536 | 
               ord( $data[$offset+3] ) * 16777216;
    }

    // sets the value into a given binary data string as a double word
    public static function putDWord( string $data, int $offset, int $value ) : void {
        $data[$offset] = chr( $value % 256 );
        $data[$offset+1] = chr( ($value % 65536 ) / 256 );
        $data[$offset+2] = chr( ($value % 16777216 ) / 65536 );
        $data[$offset+2] = chr( $value / 16777216 );
    }

    public static function timeToFido( int $unixtime ) : string
    {
       // echo "<h6> timeToFido Date Time = 0 </h6>";

        $dt = DateTime::createFromFormat( "U", $unixtime );
        return $dt->format("d M y  H:i:s");
    }

    public static function lookupEncoder( string $chrset ) : string 
    {
        switch( $chrset ) {
        /*
            Text from FTS-5003

            CP437       IBM codepage 437 (DOS Latin US)
            CP850       IBM codepage 850 (DOS Latin 1)
            CP852       IBM codepage 852 (DOS Latin 2)
            CP866       IBM codepage 866 (Cyrillic Russian)
            CP848       IBM codepage 848 (Cyrillic Ukrainian)
            CP1250      Windows, Eastern Europe
            CP1251      Windows, Cyrillic
            CP1252      Windows, Western Europe
            CP10000     Macintosh Roman character set
          
            LATIN-1     ISO 8859-1 (Western European)
            LATIN-2     ISO 8859-2 (Eastern European)
            LATIN-5     ISO 8859-9 (Turkish)
            LATIN-9     ISO 8859-15 (Western Europe with EURO sign)
          
            Level 2 obsolete character set identifiers (see note)
          
            IBMPC       IBM PC character sets for European
            +7_FIDO     IBM codepage 866, use CP866 instead
            MAC         Macintosh character set, use CPxxxxx instead
        */

            case "ASCII" : return "ASCII";
            case "IBMPC" :
            case "CP437" :
            case "PC-8":
            case "CP850" : return "CP850";

            case "LATIN-2":
            case "CP852" : return "ISO-8859-2";

            case "CP848" : return "ISO-8859-5";
        
            case "+7_FIDO":
            case "CP866" : return "CP866";

            case "LATIN-1":
            case "CP1250" : return "ISO-8859-1";
            case "CP1251" : return "CP1251";
            case "CP1252" : return "CP1252";

            case "LATIN-5" : return "ISO-8859-9";
            case "LATIN-9" : return "ISO-8859-15";

            case "MAC" : 
            case "CP10000" : return "BIG-5";

            case "KOI8":
            case "KOI8-R": return "ISO-10646-UCS-4";

            case "UTF8":
            case "UTF-8": return "UTF-8";

            default : return $chrset;
        }
    }
}