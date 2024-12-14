<?php
class TNFS{

    // 2019 - Bernat Grau (fastofruto)

    // -----------------------------------------------------------------
    // OPERATIONS ------------------------------------------------------
    // -----------------------------------------------------------------

    
    private static $TIMEOUT = 6;  // seconds
    // CONNECTION MANAGEMENT
    private static $CMD_MOUNT    = 0x00;
    private static $CMD_UMOUNT   = 0x01;

    // DIRECTORIES 
    private static $CMD_OPENDIR  = 0x10;
    private static $CMD_READDIR  = 0x11;
    private static $CMD_CLOSEDIR = 0x12;
    private static $CMD_MKDIR    = 0x13;
    private static $CMD_RMDIR    = 0x14;

     // DEVICES
    private static $CMD_SIZE     = 0x30;   // NOT IMPLEMENTED ON THE TNFS SERVER BY DYLAN SMITH ??
    private static $CMD_FREE     = 0x31;   // NOT IMPLEMENTED ON THE TNFS SERVER BY DYLAN SMITH ??
    
    // FILES
    private static $CMD_OPENFILE = 0x29;
    private static $CMD_READ     = 0x21;
    private static $CMD_WRITE    = 0x22;
    private static $CMD_CLOSE    = 0x23;
    private static $CMD_STAT     = 0x24;
    private static $CMD_LSEEK    = 0x25;
    private static $CMD_UNLINK   = 0x26;
    private static $CMD_CHMOD    = 0x27;
    private static $CMD_RENAME   = 0x28;
    
    // MAX BLOCK SIZE WHEN READING A FILE
    private static $READ_BLOCK_SIZE = 256;   // max bytes = 256 to read by read function

    // OPERATION NAMES
    private static $COMMANDS = array(
        0x00 => "MOUNT", 0x01 => "UMOUNT", 0x10 => "OPENDIR", 0x11 => "READDIR", 0x12 => "CLOSEDIR", 0x13 => "MKDIR", 0x14 => "RMDIR",
        0x29 => "OPENFILE",0x21 => "READ",0x22 => "WRITE",0x23 => "CLOSE", 0x24 => "STAT", 0x25 => "LSEEK", 0x26 => "UNLINK",
        0x27 => "CHMOD", 0x28 => "RENAME", 0x30 => "SIZE", 0x31 => "FREE"
    );

    // LIST OF VALID RETURN CODES
    public static $RET_SUCCESS           = 0x00;
    public static $RET_EPERM             = 0x01;
    public static $RET_ENOENT            = 0x02;
    public static $RET_EIO               = 0x03;
    public static $RET_ENXIO             = 0x04;
    public static $RET_E2BIG             = 0x05;
    public static $RET_EBADF             = 0x06;
    public static $RET_EAGAIN            = 0x07;
    public static $RET_ENOMEM            = 0x08;
    public static $RET_EACCES            = 0x09;
    public static $RET_EBUSY             = 0x0A;
    public static $RET_EEXIST            = 0x0B;
    public static $RET_ENOTDIR           = 0x0C;
    public static $RET_EISDIR            = 0x0D;
    public static $RET_EINVAL            = 0x0E;
    public static $RET_ENFILE            = 0x0F;
    public static $RET_EMFILE            = 0x10;
    public static $RET_EFBIG             = 0x11;
    public static $RET_ENOSPC            = 0x12;
    public static $RET_ESPIPE            = 0x13;
    public static $RET_EROFS             = 0x14;
    public static $RET_ENAMETOOLONG      = 0x15;
    public static $RET_ENOSYS            = 0x16;
    public static $RET_ENOTEMPTY         = 0x17;
    public static $RET_ELOOP             = 0x18;
    public static $RET_ENODATA           = 0x19;
    public static $RET_ENOSTR            = 0x1A;
    public static $RET_EPROTO            = 0x1B;
    public static $RET_EBADFD            = 0x1C;
    public static $RET_EUSERS            = 0x1D;
    public static $RET_ENOBUFS           = 0x1E;
    public static $RET_EALREADY          = 0x1F;
    public static $RET_ESTALE            = 0x20;
    public static $RET_EOF               = 0x21;
    public static $RET_INVALIDTNFSHANDLE = 0xFF;

    public static $RESPONSE = array(
        0x00 => "Success", 
        0x01 => "Operation not permitted", 
        0x02 => "No such file or directory", 
        0x03 => "I/O error", 
        0x04 => "No such device or address", 
        0x05 => "Argument list too long", 
        0x06 => "Bad file number", 
        0x07 => "Try again", 
        0x08 => "Out of memory", 
        0x09 => "Permission denied", 
        0x0A => "Device or resource busy", 
        0x0B => "File exists", 
        0x0C => "Is not a directory", 
        0x0D => "Is a directory", 
        0x0E => "Invalid argument", 
        0x0F => "File table overflow", 
        0x10 => "Too many open files", 
        0x11 => "File too large", 
        0x12 => "No space left on device", 
        0x13 => "Attempt to seek on a FIFO or pipe", 
        0x14 => "Read only filesystem", 
        0x15 => "Filename too long", 
        0x16 => "Function not implemented", 
        0x17 => "Directory not empty", 
        0x18 => "Too many symbolic links encountered", 
        0x19 => "No data available", 
        0x1A => "Out of streams resources", 
        0x1B => "Protocol error", 
        0x1C => "File descriptor in bad statE", 
        0x1D => "Too many users", 
        0x1E => "No buffer space available", 
        0x1F => "Operation already in progress", 
        0x20 => "Stale TNFS handle", 
        0x21 => "End of file", 
        0xFF => "Invalid TNFS handle"
    );    

    // FILE FLAGS
    private static $O_RDONLY  = 0x0001;      // Open read only
    private static $O_WRONLY  = 0x0002;      // Open write only
    private static $O_RDWR    = 0x0003;      // Open read/write
    private static $O_APPEND  = 0x0008;      // Append to the file, if it exists (write only)
    private static $O_CREAT   = 0x0100;      // Create the file if it doesn't exist (write only)
    private static $O_TRUNC   = 0x0200;      // Truncate the file on open for writing
    private static $O_EXCL    = 0x0400;      // With O_CREAT, returns an error if the file exists

    // FILE MODES
    public static $S_ALL      = 00777;        // custom 777 (rwxrwxrwx)
    public static $S_IFMT     = 0170000;     // Bitmask for the file type bitfields
    public static $S_IFSOCK   = 0140000;     // Is a socket
    public static $S_IFLNK    = 0120000;     // Is a symlink
    public static $S_IFREG    = 0100000;     // Is a regular file
    public static $S_IFBLK    = 0060000;     // block device
    public static $S_IFDIR    = 0040000;     // Directory
    public static $S_IFCHR    = 0020000;     // Character device
    public static $S_IFIFO    = 0010000;     // FIFO
    public static $S_ISUID    = 0004000;     // set UID bit
    public static $S_ISGID    = 0002000;     // set group ID bit
    public static $S_ISVTX    = 0001000;     // sticky bit
    public static $S_IRWXU    = 00700;       // Mask for file owner permissions
    public static $S_IRUSR    = 00400;       // owner has read permission
    public static $S_IWUSR    = 00200;       // owner has write permission
    public static $S_IXUSR    = 00100;       // owner has execute permission
    public static $S_IRGRP    = 00040;       // group has read permission
    public static $S_IWGRP    = 00020;       // group has write permission
    public static $S_IXGRP    = 00010;       // group has execute permission
    public static $S_IROTH    = 00004;       // others have read permission
    public static $S_IWOTH    = 00002;       // others have write permission
    public static $S_IXOTH    = 00001;       // others have execute permission



    public $DEBUG             = false;
    public $CONNECTION_ID     = 0;
    public $BUFFER            = "";
    public $CONNECTED         = false;
    public $SERVER_IP         = 0;
    public $SERVER_PORT       = 0;
    public $SEQUENCE          = 0;  // sequence of call

    private $socket           = null;

    // -----------------------------------------------------------------
    // STANDARD HEADER -------------------------------------------------
    // Bytes 0,1       Connection ID (ignored for client's "mount" command)
    // Byte  2         Retry number (sequence)
    // Byte  3         Command
    // Byte  4         Return code (for all operations)

    private $STANDARD_HEADER  = 'v1ConnectionID/C1Sequence/C1Command/C1Code/';

    // TYPE FILE / DIRECTORY
    public static $TYPE_FILE  = 0x00;
    public static $TYPE_DIR   = 0x01;

    // -----------------------------------------------------------------

    public function __construct($SERVER_IP, $SERVER_PORT){
        
        $this->SERVER_IP = $SERVER_IP;
        $this->SERVER_PORT = $SERVER_PORT;
        $this->SEQUENCE = 0;
        $this->CONNECTED = true;

        // create socket
        $this->createSocket();

        if($this->socket == null){
            $this->CONNECTED = false;
        }
    }

    public function __sleep(){
        return array('DEBUG', 'CONNECTION_ID', 'BUFFER', 'CONNECTED', 'SERVER_IP', 'SERVER_PORT', 'SEQUENCE');    
    }

    public function __wakeup(){
        $this->createSocket();
        if($this->socket == null){
            $this->CONNECTED = false;
        }
    }

    // -----------------------------------------------------------------
    // DEVICE
    

    /*
    SIZE - Requests the size of the mounted filesystem - Command 0x30
    -----------------------------------------------------------------
    Finds the size, in kilobytes, of the filesystem that is currently mounted.
    The request consists of a standard header and nothing more.

    Example:
    0xBEEF 0x00 0x30

    The reply is the standard header, followed by the return code, followed
    by a 32 bit little endian integer which is the size of the filesystem
    in kilobytes, for example:

    0xBEEF 0x00 0x30 0x00 0xD0 0x02 0x00 0x00 - Filesystem is 720kbytes
    0xBEEF 0x00 0x30 0xFF - Request failed with error code 0xFF
    */
    
    public function size(){

        if($this->socket == null) return;

        // standard header only
        $msg = $this->header(TNFS::$CMD_SIZE);

        $this->send($msg);        
        $ret = $this->receive(256);   

        //$response = $this->parseResponse($this->BUFFER, 'VFilesystemSize/');
        $response = $this->parseResponse($this->BUFFER);
       
        $this->sequence();

        return $response;
    }

    /*
    FREE - Requests the amount of free space on the filesystem - Command 0x31
    -------------------------------------------------------------------------
    Finds the size, in kilobytes, of the free space remaining on the mounted
    filesystem. The request consists of the standard header and nothing more.

    Example:
    0xBEEF 0x00 0x31

    The reply is as for SIZE - the standard header, return code, and little
    endian integer for the free space in kilobytes, for example:

    0xBEEF 0x00 0x31 0x00 0x64 0x00 0x00 0x00 - There is 64K free.
    0xBEEF 0x00 0x31 0x1F - Request failed with error 0x1F
    */

    public function free(){

        if($this->socket == null) return;

        // standard header only
        $msg = $this->header(TNFS::$CMD_FREE);

        $this->send($msg);        
        $ret = $this->receive(256);            

        //$response = $this->parseResponse($this->BUFFER, 'VFilesystemFree/');
        $response = $this->parseResponse($this->BUFFER);
       
        $this->sequence();

        return $response;
    }
    


    /*
    TNFS command datagrams
    ======================

    Logging on and logging off a TNFS server - MOUNT and UMOUNT commands.
    ---------------------------------------------------------------------
    
    
    MOUNT - Command ID 0x00
    -----------------------

    Format:
    Standard header followed by:
    Bytes 4+: 16 bit version number, little endian, LSB = minor, MSB = major
              NULL terminated string: mount location
              NULL terminated string: user id (optional - NULL if no user id)
              NULL terminated string: password (optional - NULL if no passwd)

    Example:

    To mount /home/tnfs on the server, with user id "example" and password of
    "password", using version 1.2 of the protocol:
    0x0000 0x00 0x00 0x02 0x01 /home/tnfs 0x00 example 0x00 password 0x00

    To mount "a:" anonymously, using version 1.2 of the protocol:
    0x0000 0x00 0x00 0x02 0x01 a: 0x00 0x00 0x00

    The server responds with the standard header. If the operation was successful,
    the standard header contains the session number, and the TNFS protocol
    version that the server is using following the header, followed by the
    minimum retry time in milliseconds as a little-endian 16 bit number.
    Clients must respect this minimum retry value, especially for a server
    with a slow underlying file system such as a floppy disc, to avoid swamping
    the server. A client should also never have more than one request "in flight"
    at any one time for any operation where order is important, so for example,
    if reading a file, don't send a new request to read from a given file handle
    before completing the last request.

    Example: A successful MOUNT command was carried out, with a server that
    supports version 2.6, and has a minimum retry time of 5 seconds (5000 ms,
    hex 0x1388). Session ID is 0xBEEF:

    0xBEEF 0x00 0x00 0x00 0x06 0x02 0x88 0x13

    Example: A failed MOUNT command with error 1F for a version 3.5 server:
    0x0000 0x00 0x00 0x1F 0x05 0x03
    */
   
    public function mount($mountpoint = ""){

        if($this->socket == null) return;

        $msg = $this->header(TNFS::$CMD_MOUNT);

        //0x02 0x01 a: 0x00 0x00 0x00
        $msg .= pack('CC',0x02, 0x01).$mountpoint.pack('x').pack('CC',0x00,0x00);
        //$msg .= pack('CC',0x02, 0x01).$mountpoint.pack('x')."fasto".pack('x')."fruto".pack('x');

        $this->send($msg);
        $ret = $this->receive(9);

        $response = $this->parseResponse($this->BUFFER, 'C1VersionMinor/C1VersionMajor/v1RetryTime/');

        $this->CONNECTION_ID = $response["ConnectionID"];
        
        $this->sequence();

        return $response;
    }

    /*
    UMOUNT - Command ID 0x01
    ------------------------

    Format:
    Standard header only, containing the connection ID to terminate, 0x00 as
    the sequence number, and 0x01 as the command.

    Example:
    To UMOUNT the filesystem mounted with id 0xBEEF:

    0xBEEF 0x00 0x01

    The server responds with the standard header and a return code as byte 4.
    The return code is 0x00 for OK. Example:

    0xBEEF 0x00 0x01 0x00

    On error, byte 4 is set to the error code, for example, for error 0x1F:

    0xBEEF 0x00 0x01 0x1F
    */

    public function umount(){

        if($this->socket == null) return;

        $msg = $this->header(TNFS::$CMD_UMOUNT);
        $this->send($msg);        
        $ret = $this->receive(5);

        $response = $this->parseResponse($this->BUFFER);

        $this->sequence();

        return $response;

    }

    /*
    DIRECTORIES - Opening, Reading and Closing
    ==========================================
    Don't confuse this with the ability of having a directory heirachy. Even
    servers (such as a +3 with a floppy) that don't have heirachical filesystems
    must support cataloguing a disc, and cataloguing a disc requires opening,
    reading, and closing the catalogue. It's the only way to do it!

    OPENDIR - Open a directory for reading - Command ID 0x10
    --------------------------------------------------------

    Format:
    Standard header followed by a null terminated absolute path.
    The path delimiter is always a "/". Servers whose underlying 
    file system uses other delimiters, such as Acorn ADFS, should 
    translate. Note that any recent version of Windows understands "/" 
    to be a path delimiter, so a Windows server does not need
    to translate a "/" to a "\".
    Clients should keep track of their own current working directory.

    Example:
    0xBEEF 0x00 0x10 /home/tnfs 0x00 - Open absolute path "/home/tnfs"

    The server responds with the standard header, with byte 4 set to the
    return code which is 0x00 for success, and if successful, byte 5 
    is set to the directory handle.

    Example:
    0xBEEF 0x00 0x10 0x00 0x04 - Successful, handle is 0x04
    0xBEEF 0x00 0x10 0x1F - Failed with code 0x1F
    */
   
    public function opendir($dir){

        if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_OPENDIR);

        // custom function params
        $msg .= $dir.pack('x');

        $this->send($msg);        
        $ret = $this->receive(6);

        $response = $this->parseResponse($this->BUFFER,'C1Handle/');
       
        $this->sequence();

        return $response;
    }

    /*
    READDIR - Reads a directory entry - Command ID 0x11
    ---------------------------------------------------

    Format:
    Standard header plus directory handle.

    Example:
    0xBEEF 0x00 0x11 0x04 - Read an entry with directory handle 0x04

    The server responds with the standard header, followed by the directory
    entry. Example:

    0xBEEF 0x17 0x11 0x00 . 0x00 - Directory entry for the current working directory
    0xBEEF 0x18 0x11 0x00 .. 0x00 - Directory entry for parent
    0xBEEF 0x19 0x11 0x00 foo 0x00 - File named "foo"

    If the end of directory is reached, or another error occurs, then the
    status byte is set to the error number as for other commands.
    0xBEEF 0x1A 0x11 0x21 - EOF
    0xBEEF 0x1B 0x11 0x1F - Error code 0x1F
    */
   
    public function readdir($handle){

        if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_READDIR);

        // custom function params    
        $msg .= pack('C', $handle);

        $this->send($msg);        
        $ret = $this->receive(600);

        $response = $this->parseResponse($this->BUFFER,'A*Filename/');

        $this->sequence();

        return $response;
    }

    /*
    CLOSEDIR - Close a directory handle - Command ID 0x12
    -----------------------------------------------------

    Format:
    Standard header plus directory handle.

    Example, closing handle 0x04:
    0xBEEF 0x00 0x12 0x04

    The server responds with the standard header, with byte 4 set to the
    return code which is 0x00 for success, or something else for an error.
    Example:
    0xBEEF 0x00 0x12 0x00 - Close operation succeeded.
    0xBEEF 0x00 0x12 0x1F - Close failed with error code 0x1F
    */
   
    public function closedir($handle){

        if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_CLOSEDIR);

        // custom function params
        $msg .= pack('C', $handle);

        $this->send($msg);        
        $ret = $this->receive(6);

        $response = $this->parseResponse($this->BUFFER);
       
        $this->sequence();

        return $response;
    }

    /*
    MKDIR - Make a new directory - Command ID 0x13
    ----------------------------------------------

    Format:
    Standard header plus a null-terminated absolute path.

    Example:
    0xBEEF 0x00 0x13 /foo/bar/baz 0x00

    The server responds with the standard header plus the return code:
    0xBEEF 0x00 0x13 0x00 - Directory created successfully
    0xBEEF 0x00 0x13 0x02 - Directory creation failed with error 0x02
    */

    public function mkdir($dir){

        if($this->socket == null) return;

        // standard header
        
        $msg = $this->header(TNFS::$CMD_MKDIR);

        // custom function params
        $msg .= $dir.pack('x');

        $this->send($msg);        
        $ret = $this->receive(6);    

        $response = $this->parseResponse($this->BUFFER);
        $response["Filename"] = $dir;
       
        $this->sequence();

        return $response;
    }

    /*
    RMDIR - Remove a directory - Command ID 0x14
    --------------------------------------------

    Format:
    Standard header plus a null-terminated absolute path.

    Example:
    0xBEEF 0x00 0x14 /foo/bar/baz 0x00

    The server responds with the standard header plus the return code:
    0xBEEF 0x00 0x14 0x00 - Directory was deleted.
    0xBEEF 0x00 0x14 0x02 - Directory delete operation failed with error 0x02
    */

    public function rmdir($dir){

        if($this->socket == null) return;

        // standard header
        
        $msg = $this->header(TNFS::$CMD_RMDIR);

        // custom function params
        $msg .= $dir.pack('x');

        $this->send($msg);        
        $ret = $this->receive(6);    

        $response = $this->parseResponse($this->BUFFER);
        $response["Filename"] = $dir;
       
        $this->sequence();

        return $response;
    }

    /*
    FILE OPERATIONS
    ===============
    These typically follow the low level fcntl syscalls in Unix (and Win32),
    rather than stdio and carry the same names. Note that the z88dk low level
    file operations also implement these system calls. Also, some calls,
    such as CREAT don't have their own packet in tnfs since they can be
    implemented by something else (for example, CREAT is equivalent
    to OPEN with the O_CREAT flag). Not all servers will support all flags
    for OPEN, but at least O_RDONLY. The mode refers to UNIX file permissions,
    see the CHMOD command below.

    OPEN - Opens a file - Command 0x29
    ----------------------------------
    Format: Standard header, flags, mode, then the null terminated filename.
    Flags are a bit field.

    The flags are:
    O_RDONLY        0x0001  Open read only
    O_WRONLY        0x0002  Open write only
    O_RDWR          0x0003  Open read/write
    O_APPEND        0x0008  Append to the file, if it exists (write only)
    O_CREAT         0x0100  Create the file if it doesn't exist (write only)
    O_TRUNC         0x0200  Truncate the file on open for writing
    O_EXCL          0x0400  With O_CREAT, returns an error if the file exists

    The modes are the same as described by CHMOD (i.e. POSIX modes). These
    may be modified by the server process's umask. The mode only applies
    when files are created (if the O_CREAT flag is specified)

    Examples: 
    Open a file called "/foo/bar/baz.bas" for reading:

    0xBEEF 0x00 0x29 0x0001 0x0000 /foo/bar/baz.bas 0x00

    Open a file called "/tmp/foo.dat" for writing, creating the file but
    returning an error if it exists. Modes set are S_IRUSR, S_IWUSR, S_IRGRP
    and S_IWOTH (read/write for owner, read-only for group, read-only for
    others):

    0xBEEF 0x00 0x29 0x0102 0x01A4 /tmp/foo.dat 0x00

    The server returns the standard header and a result code in response.
    If the operation was successful, the byte following the result code
    is the file descriptor:

    0xBEEF 0x00 0x29 0x00 0x04 - Successful file open, file descriptor = 4
    0xBEEF 0x00 0x29 0x01 - File open failed with "permssion denied"

    (HISTORICAL NOTE: OPEN used to have command id 0x20, but with the
    addition of extra flags, the id was changed so that servers could
    support both the old style OPEN and the new OPEN)
    */
   
    public function open($file, $flags = "w", $mode = 0x0777){
        
        if($this->socket == null) return;

        /*
            r  = Open a file for reading. If a file is in reading mode, then no data is deleted if a file is already present on a system.
            w  = Open a file for writing. If a file is in writing mode, then a new file is created if a file doesn't exist at all. If a file is already present on a system, then all the data inside the file is truncated, and it is opened for writing purposes.
            a  = Open a file in append mode. If a file is in append mode, then the file is opened. The content within the file doesn't change.
            r+ = open for reading and writing from beginning
            w+ = open for reading and writing, overwriting a file
            a+ = open for reading and writing, appending to file
        */
         

        // standard header
        $msg = $this->header(TNFS::$CMD_OPENFILE);
        
        switch($flags){
            case "r"  : $flags = TNFS::$O_RDONLY; break;
            case "w"  : $flags = TNFS::$O_WRONLY | TNFS::$O_CREAT | TNFS::$O_TRUNC; break;
            case "a"  : $flags = TNFS::$O_WRONLY | TNFS::$O_CREAT | TNFS::$O_APPEND; break;
            case "r+" : $flags = TNFS::$O_RDWR | TNFS::$O_CREAT; break;
            case "w+" : $flags = TNFS::$O_RDWR | TNFS::$O_CREAT | TNFS::$O_TRUNC; break;
            case "a+" : $flags = TNFS::$O_RDWR | TNFS::$O_CREAT | TNFS::$O_APPEND; break;
            default   : $flags = TNFS::$O_RDWR; break;
        }

        // custom function params
        $msg .= pack('vv', $flags, $mode).$file.pack('x');

        $this->send($msg);        
        $ret = $this->receive(6);

        $response = $this->parseResponse($this->BUFFER,'C1Handle/');


        $this->sequence();

        return $response;
    }
    
    /*
    READ - Reads from a file - Command 0x21
    ---------------------------------------
    Reads a block of data from a file. Consists of the standard header
    followed by the file descriptor as returned by OPEN, then a 16 bit
    little endian integer specifying the size of data that is requested.

    The server will only reply with as much data as fits in the maximum
    TNFS datagram size of 1K when using UDP as a transport. For the
    TCP transport, sequencing and buffering etc. are just left up to
    the TCP stack, so a READ operation can return blocks of up to 64K. 

    If there is less than the size requested remaining in the file, 
    the server will return the remainder of the file.  Subsequent READ 
    commands will return the code EOF.

    Examples:
    Read from fd 4, maximum 256 bytes:

    0xBEEF 0x00 0x21 0x04 0x00 0x01

    The server will reply with the standard header, followed by the single
    byte return code, the actual amount of bytes read as a 16 bit unsigned
    little endian value, then the data, for example, 256 bytes:

    0xBEEF 0x00 0x21 0x00 0x00 0x01 ...data...

    End-of-file reached:

    0xBEEF 0x00 0x21 0x21
    */
    
    public function read($handle, $length){
        if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_READ);

        // custom function params    
        $msg .= pack('CV', $handle, $length);

        $this->send($msg);        
        $ret = $this->receive(512);


        $response = $this->parseResponse($this->BUFFER,'vLength/a*Content/');

       
        $this->sequence();

        return $response;
    }

    /*
    READFILE - Reads an entire file and returns it as a string
    ----------------------------------------------------------
    Requires the handle to the file and the size of the file
    
    Returns the file content as string.
    */
    public function readFile($handle, $filesize){
        $file_content = "";
        $pos = 0;
        do {
            $res = $this->read($handle, TNFS::$READ_BLOCK_SIZE);
            //var_dump($res);
            if($res["Code"] != TNFS::$RET_EOF){
                $file_content .= $res["Content"];
                $pos = $pos + $res["Length"];
                if($pos < $filesize){
                    $this->lseek($handle, $pos);
                } else {
                    break;
                }
            }
        } while($res["Code"] != TNFS::$RET_EOF);

        return $file_content;
    }


    /*
    WRITE - Writes to a file - Command 0x22
    ---------------------------------------
    Writes a block of data to a file. Consists of the standard header,
    followed by the file descriptor, followed by a 16 bit little endian
    value containing the size of the data, followed by the data. The
    entire message must fit in a single datagram.

    Examples:
    Write to fd 4, 256 bytes of data:

    0xBEEF 0x00 0x22 0x04 0x00 0x01 ...data...

    The server replies with the standard header, followed by the return
    code, and the number of bytes actually written. For example:

    0xBEEF 0x00 0x22 0x00 0x00 0x01 - Successful write of 256 bytes
    0xBEEF 0x00 0x22 0x06 - Failed write, error is "bad file descriptor"
    
    */
    
    public function write($handle, $data){
         if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_WRITE);

        // custom function params    
        $msg .= pack('Cva*', $handle, strlen($data), $data);

        $this->send($msg);        
        $ret = $this->receive(256);

        $response = $this->parseResponse($this->BUFFER,'vLength/');

        $this->sequence();

        return $response;
    }

    /*
    CLOSE - Closes a file - Command 0x23
    ------------------------------------
    Closes an open file. Consists of the standard header, followed by
    the file descriptor. Example:

    0xBEEF 0x00 0x23 0x04 - Close file descriptor 4

    The server replies with the standard header followed by the return
    code:

    0xBEEF 0x00 0x23 0x00 - File closed.
    0xBEEF 0x00 0x23 0x06 - Operation failed with EBADF, "bad file descriptor"
    */
   
    public function close($handle){
         if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_CLOSE);

        // custom function params    
        $msg .= pack('C', $handle);

        $this->send($msg);        
        $ret = $this->receive(256);


        $response = $this->parseResponse($this->BUFFER);

        $this->sequence();

        return $response;
    }

    /*
    STAT - Get information on a file - Command 0x24
    -----------------------------------------------
    Reads the file's information, such as size, datestamp etc. The TNFS
    stat contains less data than the POSIX stat - information that is unlikely
    to be of use to 8 bit systems are omitted.
    The request consists of the standard header, followed by the full path
    of the file to stat, terminated by a NULL. Example:

    0xBEEF 0x00 0x24 /foo/bar/baz.txt 0x00

    The server replies with the standard header, followed by the return code.
    On success, the file information follows this. Stat information is returned
    in this order. Not all values are used by all servers. At least file
    mode and size must be set to a valid value (many programs depend on these).

    File mode       - 2 bytes: file permissions - little endian byte order
    uid             - 2 bytes: Numeric UID of owner
    gid             - 2 bytes: Numeric GID of owner
    size            - 4 bytes: Unsigned 32 bit little endian size of file in bytes
    atime           - 4 bytes: Access time in seconds since the epoch, little end.
    mtime           - 4 bytes: Modification time in seconds since the epoch,
                               little endian
    ctime           - 4 bytes: Time of last status change, as above.
    uidstring       - 0 or more bytes: Null terminated user id string
    gidstring       - 0 or more bytes: Null terminated group id string

    Fields that don't apply to the server in question should be left as 0x00.
    The ´mtime' field and 'size' fields are unsigned 32 bit integers.
    The uidstring and gidstring are helper fields so the client doesn't have
    to then ask the server for the string representing the uid and gid.

    File mode flags will be most useful for code that is showing a directory
    listing, and for programs that need to find out what kind of file (regular
    file or directory, etc) a particular file may be. They follow the POSIX
    convention which is:

    Flags           Octal representation
    S_IFMT          0170000         Bitmask for the file type bitfields
    S_IFSOCK        0140000         Is a socket
    S_IFLNK         0120000         Is a symlink
    S_IFREG         0100000         Is a regular file
    S_IFBLK         0060000         block device
    S_IFDIR         0040000         Directory
    S_IFCHR         0020000         Character device
    S_IFIFO         0010000         FIFO
    S_ISUID         0004000         set UID bit
    S_ISGID         0002000         set group ID bit
    S_ISVTX         0001000         sticky bit
    S_IRWXU         00700           Mask for file owner permissions
    S_IRUSR         00400           owner has read permission
    S_IWUSR         00200           owner has write permission
    S_IXUSR         00100           owner has execute permission
    S_IRGRP         00040           group has read permission
    S_IWGRP         00020           group has write permission
    S_IXGRP         00010           group has execute permission
    S_IROTH         00004           others have read permission
    S_IWOTH         00002           others have write permission
    S_IXOTH         00001           others have execute permission

    Most of these won't be of much interest to an 8 bit client, but the
    read/write/execute permissions can be used for a client to determine whether
    to bother even trying to open a remote file, or to automatically execute
    certain types of files etc. (Further file metadata such as load and execution
    addresses are platform specific and should go into a header of the file
    in question). Note the "trivial" bit in TNFS means that the client is
    unlikely to do anything special with a FIFO, so writing to a file of that
    type is likely to have effects on the server, and not the client! It's also
    worth noting that the server is responsible for enforcing read and write
    permissions (although the permission bits can help the client work out
    whether it should bother to send a request).
    */

    public function stat($filename){

        if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_STAT);

        // custom function params
        $msg .= $filename.pack('x');

        $this->send($msg);        
        $ret = $this->receive(60);

        $response = $this->parseResponse($this->BUFFER,'vFileMode/vUID/vGID/VSize/VATime/VMTime/VCTime/A*UIDString/A*GIDString');

        // is file or directory ?
        if($response["FileMode"] & TNFS::$S_IFDIR){ $response["Type"] = TNFS::$TYPE_DIR; $response["Size"] = "";}
        if($response["FileMode"] & TNFS::$S_IFREG){ $response["Type"] = TNFS::$TYPE_FILE; }
       
        $this->sequence();

        return $response;
    }

    /*
    LSEEK - Seeks to a new position in a file - Command 0x25
    --------------------------------------------------------
    Seeks to an absolute position in a file, or a relative offset in a file,
    or to the end of a file.
    The request consists of the header, followed by the file descriptor,
    followed by the seek type (SEEK_SET, SEEK_CUR or SEEK_END), followed
    by the position to seek to. The seek position is a signed 32 bit integer,
    little endian. (2GB file sizes should be more than enough for 8 bit
    systems!)

    The seek types are defined as follows:
    0x00            SEEK_SET - Go to an absolute position in the file
    0x01            SEEK_CUR - Go to a relative offset from the current position
    0x02            SEEK_END - Seek to EOF

    Example:

    File descriptor is 4, type is SEEK_SET, and position is 0xDEADBEEF:
    0xBEEF 0x00 0x25 0x04 0x00 0xEF 0xBE 0xAD 0xDE

    Note that clients that buffer reads for single-byte reads will have
    to make a calculation to implement SEEK_CUR correctly since the server's
    file pointer will be wherever the last read block made it end up.
    */
   
    public function lseek($handle, $pos){
         if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_LSEEK);

        // custom function params    
        $msg .= pack('CCV', $handle, 0x00, $pos);

        $this->send($msg);        
        $ret = $this->receive(256);


        $response = $this->parseResponse($this->BUFFER);

        $this->sequence();

        return $response;
    }

    /*
    UNLINK - Unlinks (deletes) a file - Command 0x26
    ------------------------------------------------
    Removes the specified file. The request consists of the header then
    the null terminated full path to the file. The reply consists of the
    header and the return code.

    Example:
    Unlink file "/foo/bar/baz.bas"
    0xBEEF 0x00 0x26 /foo/bar/baz.bas 0x00
    */
   
    public function unlink($file){

        if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_UNLINK);

        // custom function params
        $msg .= $file.pack('x');

        $this->send($msg);        
        $ret = $this->receive(5);

        $response = $this->parseResponse($this->BUFFER);

        $this->sequence();

        return $response;
    }

    /*
    CHMOD - Changes permissions on a file - Command 0x27
    ----------------------------------------------------
    Changes file permissions on the specified file, using POSIX permissions
    semantics. Not all permissions may be supported by all servers - most 8
    bit systems, for example, may only support removing the write bit.
    A server running on something Unixish will support everything.
    The request consists of the header, followed by the 16 bit file mode,
    followed by the null terminated filename. Filemode is sent as a little
    endian value. See the Unix manpage for chmod(2) for further information.

    File modes are as defined by POSIX. The POSIX definitions are as follows:
                  
    Flag      Octal Description
    S_ISUID   04000 set user ID on execution
    S_ISGID   02000 set group ID on execution
    S_ISVTX   01000 sticky bit
    S_IRUSR   00400 read by owner
    S_IWUSR   00200 write by owner
    S_IXUSR   00100 execute/search by owner
    S_IRGRP   00040 read by group
    S_IWGRP   00020 write by group
    S_IXGRP   00010 execute/search by group
    S_IROTH   00004 read by others
    S_IWOTH   00002 write by others
    S_IXOTH   00001 execute/search by others

    Example: Set permissions to 755 on /foo/bar/baz.bas:
    0xBEEF 0x00 0x27 0xED 0x01 /foo/bar/baz.bas

    The reply is the standard header plus the return code of the chmod operation.
    */
   

   
    public function chmod($file, $permissions = 0755){
         if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_CHMOD);

        // custom function params
        //$msg .= pack('v',$permissions);//.$file.pack('x');

        $this->send($msg);        

        $ret = $this->receive(20);
        //var_dump($this->BUFFER);die();

        $response = $this->parseResponse($this->BUFFER);

        $this->sequence();

        return $response;
    }

    /*
    RENAME - Moves a file within a filesystem - Command 0x28
    --------------------------------------------------------
    Renames a file (or moves a file within a filesystem - it must be possible
    to move a file to a different directory within the same FS on the
    server using this command).
    The request consists of the header, followed by the null terminated
    source path, and the null terminated destination path.

    Example: Move file "foo.txt" to "bar.txt"
    0xBEEF 0x00 0x28 foo.txt 0x00 bar.txt 0x00
    */
   
    public function rename($file, $newname){
         if($this->socket == null) return;

        // standard header
        $msg = $this->header(TNFS::$CMD_RENAME);

        // custom function params    
        $msg .= $file.pack('x').$newname.pack('x');

        $this->send($msg);        
        $ret = $this->receive(256);


        $response = $this->parseResponse($this->BUFFER);

        $this->sequence();

        return $response;
    }

   


    public function destroy(){
        $this->closeSocket();
    }


    // PRIVATE FUNCTIONS
    
    private function parseResponse($buffer, $format=""){
        
        /* Unpack the header data */
        $response = unpack($this->STANDARD_HEADER.$format, $buffer);

        $response["Function"] = TNFS::$COMMANDS[$response["Command"]];
        $response["Response"] = TNFS::$RESPONSE[$response["Code"]];
        
        return $response;
    }

    private function header($tnfs_command){
        return pack("vCC", $this->CONNECTION_ID, $this->SEQUENCE, $tnfs_command);
    }

    private function receive($length){
        $ret = socket_recvfrom($this->socket, $this->BUFFER, $length, 0, $this->SERVER_IP, $this->SERVER_PORT); 
        return $ret;
    }

    private function send($msg){
        socket_sendto($this->socket, $msg, strlen($msg), 0, $this->SERVER_IP, $this->SERVER_PORT);
    }

    private function createSocket(){
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => TNFS::$TIMEOUT, 'usec' => 0));
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => TNFS::$TIMEOUT, 'usec' => 0));

        if($this->socket !== FALSE){
            //socket_set_timeout($this->socket, 5);
            //socket_set_nonblock($this->socket);
            
            if($this->checkSocket() == ""){
                $this->socket = null;
                return null;
            }
        } else{
           $this->socket = null;
        }
        return $this->socket;
    }
    private function checkSocket(){
        $msg = $this->header(TNFS::$CMD_MOUNT);

        //0x02 0x01 a: 0x00 0x00 0x00
        $msg .= pack('CC',0x02, 0x01).".".pack('x').pack('CCC',0x00,0x00,0x00);

        $this->send($msg);
        $ret = $this->receive(9);

        //var_dump("retorno : $ret");

        return $ret;
    }

    private function closeSocket(){
        if($this->socket != null){
            socket_shutdown($this->socket, 2);
            socket_close($this->socket);
        }
    }

    private function sequence(){
        $this->SEQUENCE++;
    }

}

?>