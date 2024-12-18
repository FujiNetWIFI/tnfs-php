<?php
error_reporting(E_ERROR | E_PARSE);



	include("tnfs.php");
	include("config.php");
	session_start();

	function startsWith( $haystack, $needle ) {
	     $length = strlen( $needle );
	     return substr( $haystack, 0, $length ) === $needle;
	}

	function endsWith( $haystack, $needle ) {
	    $length = strlen( $needle );
	    if( !$length ) {
	        return true;
	    }
	    return substr( $haystack, -$length ) === $needle;
	}

	function connect() {
		$host = $_REQUEST["host"];
		$port = $_REQUEST["port"];
		$protocol = $_REQUEST["protocol"];
		$sid = null;
		if (isset($_SESSION['sid'])) {
			$sid = $_SESSION['sid'];
		}
		$tnfs = new TNFS($host, $port, $protocol, $sid);
		$_SESSION['sid'] = $tnfs->CONNECTION_ID;
		return $tnfs;
	}

	$host = $_REQUEST["host"];
	$port = $_REQUEST["port"];
	$protocol = $_REQUEST["protocol"];

	$root_path = "C:/spectrum";
	//$root_path = "/var/www/html/tnfs";

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "UPLOAD"){
		//var_dump($_REQUEST);die();
		//var_dump($_FILES);var_dump($root_path."/".$path);die();
		if(!empty($_FILES['upload-file-element'])){
			$path = basename( $_FILES['upload-file-element']['name']);			
			$name = $_FILES['upload-file-element']['name'];
				
			$file_contents = file_get_contents($_FILES['upload-file-element']['tmp_name']);

			$path = $_REQUEST["path"];
			$tnfs = connect();
			// open file		
			$res = $tnfs->open($path."/".$name, "w+", TNFS::$S_ALL);
			$handle = $res["Handle"];
			
			// read all content and return as string
			$content = $tnfs->write($handle, $file_contents);
			$tnfs->close($handle);
			
			echo "1";
	
		}
		die();
	}


	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "OPENDIR"){
		$path = $_REQUEST["path"];
		$tnfs = connect();
		$directory = $tnfs->opendir($path);
		if($directory != null && $directory["Code"] == TNFS::$RET_SUCCESS){ ?>

			<form id="form-upload" enctype="multipart/form-data" style="display: none !important">
			    <input type="hidden" name="host" value="<?php echo $host;?>"/>
			    <input type="hidden" name="port" value="<?php echo $port;?>"/>
			    <input type="hidden" name="protocol" value="<?php echo $protocol;?>"/>
			    <input type="hidden" name="path" value="<?php echo $path;?>"/>
			    <input name="upload-file-element" id="upload-file-element" type="file" />
			    <input type="button" id="btn-upload-file" value="Upload" />
			    <!--<progress></progress>-->
			</form>

			<div class="table-responsive-sm">

				<div class="btn-group" role="group" style="margin-bottom: 10px">			  
					<div class="btn-group mr-2" role="group">
						<a href="#" class="btn bmagenta white" id="btn-new-folder">New folder</a>
					</div>
					<div class="btn-group mr-2" role="group">
						<a href="#" class="btn bmagenta white" id="btn-fake-upload">Upload file</a>
					</div>
				</div>

				<table id="table-explorer" class="list table table-striped table-hover table-bordered table-sm">
					<thead>
						<tr>
							<th colspan="4" class="bblue white"><?php echo $path;?></th>
							<!--<th class="bwhite white" style="padding: 0"><a id="btn-upload" class="btn bblue white btn-block btn-upload" style="padding: 3px 10px;">Upload</a></th>-->		
							
						</tr>
						<tr>
							<th class="d-none d-md-table-cell" style="width:100px">Type</th>
							<th>Name</th>
							<th style="width:148px !important">Options</th>
							<th class="d-none d-md-table-cell">Size</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$dirlist = array();
						$filelist = array();
						
						do{
						    $read = $tnfs->readdir($directory["Handle"]);
						    
						    if($read["Code"] != TNFS::$RET_EOF){
						        if($read["Filename"] == "." || $read["Filename"] == ".."){
						            continue;
						        }
						        
					        	if($path != "/"){
					        		$read["Fullpath"] = $path."/".$read["Filename"];
					        	} else {
					        		$read["Fullpath"] = $path.$read["Filename"];
					        	}
//								var_dump( $read["Filename"]);
//					        	var_dump( $read["Fullpath"]);

						        $res = $tnfs->stat($read["Fullpath"]);
						        $read["Type"] = $res["Type"];
						        $read["Size"] = $res["Size"];
						        if($res["Type"] == TNFS::$TYPE_DIR)
						    		$dirlist[] = $read;
						    	if($res["Type"] == TNFS::$TYPE_FILE)
						    		$filelist[] = $read;
						    }
						    
						} while($read["Code"] != TNFS::$RET_EOF);
						$tnfs->closedir($directory["Handle"]);

						
						array_multisort( array_column($dirlist, "Filename"), SORT_ASC | SORT_FLAG_CASE | SORT_NATURAL , $dirlist );
						array_multisort( array_column($filelist, "Filename"), SORT_ASC | SORT_FLAG_CASE | SORT_NATURAL, $filelist );
						$dirlist = array_merge($dirlist, $filelist);
						//var_dump($dirlist);

						if($path != "/"){

							$url = explode('/',$path);
							array_pop($url);
							$previous_folder = implode('/', $url); 

//							var_dump($previous_folder);
							if($previous_folder == "") { $previous_folder = "/";} 

							$back = array(
								"Filename" => "..",
								"Fullpath" => $previous_folder,
								"Type" => TNFS::$TYPE_DIR,
								"Size" => ""
							);
							array_unshift($dirlist, $back);
						}
					

						foreach($dirlist as $elem){ 
							$uniqid = randhash();
							?>
							<tr id="<?php echo $uniqid;?>">
					        	<td class="d-none d-md-table-cell">
					        		<?php if($elem["Type"] == TNFS::$TYPE_DIR){ echo "[DIR]";}?>
					        	</td>
					        	
					        	<?php if($elem["Type"] == TNFS::$TYPE_DIR){ ?>
					        	<td>
					        		<a class="btn-dir white bblue bright0 f-name" data-path="<?php echo $elem["Fullpath"];?>" href="#"><?php echo $elem["Filename"];?></a>					        		
					        	</td>
					        	<td style="width:148px !important">
					        		<?php if($elem["Filename"] != "..") { ?>
					        		<div id="dropdown-dir-actions" class="dropdown" style="border:none">
									  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdown-dir-actions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									    Options
									  </button>
									  <div id="dropdown-dir-actions-list" class="dropdown-menu dropdown-menu-right" style="margin-top:0px" aria-labelledby="dropdown-dir-actions">
					        			<a data-type="1" href="#" data-fullpath="<?php echo $elem["Fullpath"];?>" data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" data-size="<?php echo $elem["Size"];?>" class="btn-rename dropdown-item host-element">Rename</a>
					        			<span href="#" class=" dropdown-item" style="padding:5px 0px"><hr style="margin:0;border-top:2px solid #000"/></span>
					        			<a href="#" data-fullpath="<?php echo $elem["Fullpath"];?>"  data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" class="btn-delete-folder dropdown-item host-element bred white" >Delete</a>
									  </div>
									</div>
									<?php } else { echo "&nbsp"; } ?>
					        	</td>
					        	<?php } else { ?>
					        	<td>
					        		<span class="f-name"><?php echo $elem["Filename"];?></span>
					        	</td>
					        	<td style="width:148px">
					        		<div id="dropdown-file-actions" class="dropdown" style="border:none">
									  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdown-file-actions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									    Options
									  </button>
									  <div id="dropdown-file-actions-list" class="dropdown-menu dropdown-menu-right" style="margin-top:0px" aria-labelledby="dropdown-file-actions">
									  	<?php if(endsWith($elem["Fullpath"], ".scr")){ ?>
									  	<a href="#" data-fullpath="<?php echo $elem["Fullpath"];?>" data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" data-size="<?php echo $elem["Size"];?>" class="btn-view-scr dropdown-item host-element">View SCR</a>
									  	<?php } ?>

									    <a href="#" data-fullpath="<?php echo $elem["Fullpath"];?>" data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" data-size="<?php echo $elem["Size"];?>" class="btn-content dropdown-item host-element">View file</a>
					        			<a href="#" data-fullpath="<?php echo $elem["Fullpath"];?>" data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" data-size="<?php echo $elem["Size"];?>" class="btn-download dropdown-item host-element">Download</a>
					        			<a data-type="0" href="#" data-fullpath="<?php echo $elem["Fullpath"];?>" data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" data-size="<?php echo $elem["Size"];?>" class="btn-rename dropdown-item host-element">Rename</a>
					        			<a data-type="0" href="#" data-fullpath="<?php echo $elem["Fullpath"];?>" data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" data-size="<?php echo $elem["Size"];?>" class="btn-move dropdown-item host-element">Move</a>
					        			<span href="#" class=" dropdown-item" style="padding:5px 0px"><hr style="margin:0;border-top:2px solid #000"/></span>
					        			<a href="#" data-fullpath="<?php echo $elem["Fullpath"];?>"  data-file="<?php echo $elem["Filename"];?>" data-hash="<?php echo $uniqid;?>" class="btn-delete dropdown-item host-element bred white" >Delete</a>
									  </div>
									</div>
								</td>		
				        		<?php } ?>
					        	<td style="width:140px" class="d-none d-md-table-cell tr"><?php echo $elem["Size"];?></td>
					        </tr>
						<?php } ?>
					</tbody>
					
				</table>
			</div>
			<?php
		} else {
			echo "0";
		}
		die();
	}

	if(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "READFILE" || $_REQUEST["action"] == "DOWNLOAD" || $_REQUEST["action"] == "VIEWSCR")){
		$file = $_REQUEST["file"];
		$fullpath = $_REQUEST["fullpath"];
		$size = $_REQUEST["size"];
		$tnfs = connect();
		// open file		
		$res = $tnfs->open($fullpath, "r", TNFS::$S_ALL);
		$handle = $res["Handle"];
		
		// read all content and return as string
		$content = $tnfs->readFile($handle, $size);
		$tnfs->close($handle);

		if($_REQUEST["action"] == "READFILE"){
			echo $content;
		}

		if($_REQUEST["action"] == "VIEWSCR"){
			file_put_contents("tmp/screen.scr", $content);			
			echo $content;
		}
		
		if($_REQUEST["action"] == "DOWNLOAD"){
			file_put_contents($file, $content);			
			echo $size;
		}

		die();
	}

	if(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "WRITEFILE")){
		$file = $_REQUEST["file"];
		$path = $_REQUEST["path"];
		$size = $_REQUEST["size"];



		$name = "upload_file.txt";
		//die($fullpath.$name);

		$tnfs = connect();
		// open file		
		$res = $tnfs->open($path."/".$name, "w+", TNFS::$S_ALL);
		$handle = $res["Handle"];
		
		// read all content and return as string
		$content = $tnfs->write($handle, "hooola");
		$tnfs->close($handle);

		//echo $content;
		
		die();
	}

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "MKDIR"){
		$path = $_REQUEST["path"];
		$name = $_REQUEST["name"];
		$tnfs = connect();
		// $file has de full path of the file to rename
		$res = $tnfs->mkdir($path."/".$name);
		if($res != null && $res["Code"] == TNFS::$RET_SUCCESS){
			echo $new_file;
		} else {
			echo "0";
		}
		die();
	}

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "RMDIR"){
		$fullpath = $_REQUEST["fullpath"];
		$tnfs = connect();
		// $file has de full path of the file to rename
		$res = $tnfs->rmdir($fullpath);

		if($res != null && $res["Code"] == TNFS::$RET_SUCCESS){
			echo "1";
		} else {
			echo "0";
		}
		die();
	}

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "RENAME"){
		$fullpath = $_REQUEST["fullpath"];
		$name = $_REQUEST["name"];
		$tnfs = connect();
		// $file has de full path of the file to rename
		// $name ony the name
		// must join together and create the fullpath with the new name
		$parts = explode('/', $fullpath);
		array_pop($parts);
		$new_file = implode('/', $parts)."/".$name;

		$response = $tnfs->rename($fullpath, $new_file);
		if($response != null && $response["Code"] == TNFS::$RET_SUCCESS){
			echo $new_file;
		} else {
			echo "0";
		}
		die();
	}

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "MOVE"){
		$fullpath = $_REQUEST["fullpath"];
		$name = $_REQUEST["name"];
		$to = $_REQUEST["to"];
		$tnfs = connect();
		$file = basename($fullpath);  

		$response = $tnfs->rename($fullpath, $to."/".$file);
		if($response != null && $response["Code"] == TNFS::$RET_SUCCESS){
			echo "1";
		} else {
			echo "0";
		}
		die();
	}



	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "DEL"){
		$file = $_REQUEST["file"];
		$tnfs = connect();
		$response = $tnfs->unlink($file);
		if($response != null && $response["Code"] == TNFS::$RET_SUCCESS){
			echo "1";
		} else {
			echo "0";
		}
		die();
	}

	$disconnect = false;

	function randHash($len=32)
	{
		return substr(md5(openssl_random_pseudo_bytes(20)),-$len);
	}

	if(isset($_REQUEST["disconnect"])){

		if($tnfs != null && isset($_REQUEST["disconnect"])){
			if($tnfs != null){
				$tnfs->umount();
				$tnfs->destroy();
				unset($_SESSION["sid"]);
				session_unset();
				session_destroy();
				$tnfs->CONNECTED == false;
				$sid = 0;
			}
		} 

	}

	//var_dump($_REQUEST);

	if(isset($_REQUEST["connect"]) ){
		$tnfs = connect();
		$connected = true;
	} else {
		$connected = false;
		$host = TNFS_HOSTS[0];
		$port = 16384;
		$protocol = "tcp";
	}

?>

<!doctype html>
<html lang="en" class="h-100">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <title>PHP-TNFS</title>

    <script type="text/javascript">
    		var path = "/";       // current path
    		var filename = "";    // only filename
    		var fullpath = "";    // full path to file (with filename);
    		var hash = "";
    		var type = 0;   // 0 = FILE   1 = DIR  // for renaming

			$(document).ready(function(){

				$(document).on("click","#btn-fake-upload", function(e){
					e.preventDefault();
					$("#upload-file-element").trigger("click");

				});

				$(document).on('change',':file', function () {
				  var file = this.files[0];
				  
				  /*if (file.size > 1024) {
				    alert('max upload size is 1k');
				  }*/

				  $("#btn-upload-file").trigger("click");
				  load();

				  // Also see .name, .type
				});

				$(document).on("click","#btn-upload-file", function () {
					
				  $.ajax({
				    // Your server script to process the upload
				    url: 'index.php?action=UPLOAD',
				    type: 'POST',
				    async: false,

				    // Form data
				    data: new FormData($('#form-upload')[0]),

				    // Tell jQuery not to process data or worry about content-type
				    // You *must* include these options!
				    cache: false,
				    contentType: false,
				    processData: false,

				    // Custom XMLHttpRequest
				    xhr: function () {
				      var myXhr = $.ajaxSettings.xhr();
				      if (myXhr.upload) {
				        // For handling the progress of the upload
				        myXhr.upload.addEventListener('progress', function (e) {
				          if (e.lengthComputable) {
				            $('progress').attr({
				              value: e.loaded,
				              max: e.total,
				            });
				          }
				        }, false);
				      }

				      return myXhr;
				    }
				  });
				  load();
				});

				$(document).on("click", "#btn-new-folder", function(e){
					e.preventDefault();
					fullpath = $(this).attr("data-fullpath")
					file = $(this).attr("data-file")
					hash = $(this).attr("data-hash");
					size = $(this).attr("data-size");
					$("#modal-new-folder").modal("show");
					$("#new-folder-name").focus();
				});

				$(document).on("click", "#btn-create-folder", function(e){
					e.preventDefault();
					
					name = $("#new-folder-name").val();

					//console.log(path);
					//console.log(name);

					$.ajax({
			            type: "POST",
			            url: 'index.php',			            
			            data: {
			            	action: "MKDIR",
			            	path: path,
			            	name: name, 
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
			            	protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function(response){
			            	$("#new-folder-name").val('');
			            },
			            success: function(response)
			            {
			            	$("#modal-new-folder").modal('hide');
			                load();
			                //$("#file-content").html(response);
			            }
			       });
				});

				$("#btn-connect").click(function(e){
					$("#info-connected").hide();
					$("#info-connect-error").hide();
					$("#info-connect-not").hide();
					$("#info-connecting").show();

					$("#dropdown-hosts").html($("#host").val());
					$("#dropdown-protocol").html($("#protocol").val());
				});

				$("#btn-disconnect").click(function(e){
					$("#info-connected").hide();
					$("#info-connect-error").hide();
					$("#info-connect-not").hide();
					$("#info-disconnecting").show();
				});

				$("#dropdown .host-element").click(function(e){
					e.preventDefault();
					$("#host").val($(this).text());
					$("#dropdown-hosts").html($(this).text());
				});

				$("#dropdown-protocol-outer .protocol-element").click(function(e){
					e.preventDefault();
					$("#protocol").val($(this).text());
					$("#dropdown-protocol").html($(this).text());
				});

				$('#dropdown').on('show.bs.dropdown', function () {
					$("#host").val("").click().focus().select().focus();;
				});
				$('#dropdown-protocol-outer').on('show.bs.dropdown', function () {
					$("#protocol").val("").click().focus().select().focus();;
				});
				
				//$(".btn-content").on("click", function(e){
				$(document).on("click", ".btn-content", function(e){
					e.preventDefault();
					fullpath = $(this).attr("data-fullpath")
					file = $(this).attr("data-file")
					hash = $(this).attr("data-hash");
					size = $(this).attr("data-size");

					$.ajax({
			            type: "POST",
			            url: 'index.php',			            
			            data: {
			            	action: "READFILE",
			            	fullpath: fullpath,
			            	size: size,
			            	file: file,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function(response){
			            	$("#file-content").html('');
			            	$("#file-name").html(file);
			                $("#modal-content").modal("show");
			            },
			            success: function(response)
			            {
			                $("#file-content").html(response);
			            }
			       });
				});

				//$(".btn-content").on("click", function(e){
				$(document).on("click", ".btn-view-scr", function(e){
					e.preventDefault();
					fullpath = $(this).attr("data-fullpath")
					file = $(this).attr("data-file")
					hash = $(this).attr("data-hash");
					size = $(this).attr("data-size");

					$.ajax({
			            type: "POST",
			            url: 'index.php',			            
			            data: {
			            	action: "VIEWSCR",
			            	fullpath: fullpath,
			            	size: size,
			            	file: file,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function(response){
			            	$("#file-content").html('');
			            	$("#file-name").html(file);
			                $("#modal-content").modal("show");
			            },
			            success: function(response)
			            {
							var s ="<div stlye='text-align:center;'><img class='scr' style='width:768px' src='scr2png.php?scrimg=tmp/screen.scr'/></div>";
			                $("#file-content").html(s);
			            }
			       });
				});


				//$(".btn-download").on("click", function(e){
				$(document).on("click", ".btn-download", function(e){
					e.preventDefault();
					fullpath = $(this).attr("data-fullpath")
					file = $(this).attr("data-file")
					hash = $(this).attr("data-hash");
					size = $(this).attr("data-size");

					$.ajax({
			            type: "POST",
			            url: 'index.php',
			            data: {
			            	action: "DOWNLOAD",
			            	async: false,
			            	fullpath: fullpath,
			            	file: file,
			            	size: size,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function() {
					        $("#modal-download-filename").html(file + " ("+size+")");
		           			$("#modal-download").modal("show");
					    },
			            success: function(response)
			            {
			            	if(typeof response != typeof undefined && response > 0){
				            	var link = document.createElement("a");
							    link.download = file;
							    link.href = file;
							    link.click();
							}

			           }
			       });
				});

				$(document).on("click", ".btn-move", function(e){
					e.preventDefault();
					fullpath = $(this).attr("data-fullpath")
					file = $(this).attr("data-file")
					hash = $(this).attr("data-hash");
					size = $(this).attr("data-size");

					$("#modal-move").modal("show");
					$("#move-to").val('').focus();
				});

				$(document).on("click", "#btn-move-file", function(e){
					
					to = $("#move-to").val();
					console.log(to);

					$.ajax({
			            type: "POST",
			            url: 'index.php',
			            data: {
			            	action: "MOVE",
			            	fullpath: fullpath,
			            	file: file,
							size: size,
			            	to: to,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function() {
		           			
					    },
			            success: function(response)
			            {
			            	if(response == "1"){
			                	$("#modal-move").modal('hide');
			                	$("tr#"+hash).remove();
			                }
			        	}
			       });
				});
				
				$(document).on("click", ".btn-rename", function(e){
					e.preventDefault();
					fullpath = $(this).attr("data-fullpath")
					file = $(this).attr("data-file")
					hash = $(this).attr("data-hash");
					size = $(this).attr("data-size");
					type = $(this).attr("data-type");

					$("#rename-filename").val("").focus();
					$("#old-file").text(file);
		           	$("#modal-rename").modal("show");

				});

				$(document).on("click", "#btn-rename-file", function(e){
					e.preventDefault();
					var name = $.trim($("#rename-filename").val());
					var btn = $(this);

					if(name != ""){
						$.ajax({
				            type: "POST",
				            async: false,
				            url: 'index.php',
				            data: {
				            	action:"RENAME",				            	
				            	fullpath: fullpath,
				            	name: name,
				            	host: "<?php echo $host;?>",
				            	port: <?php echo $port;?>,
								protocol: "<?php echo $protocol;?>"
				            },
				            success: function(response)
				            {
				            	//console.log($("tr#"+hash).find("span.span-name"))
				            	if(response !=""){
				            		$("tr#"+hash).find(".f-name").html(name);
				            		$("#modal-rename").modal('hide');
				            		// update data-file data-fullpath data-hash
				            		if(type == 0){
					            		$("a.btn-rename[data-hash='"+hash+"']").attr("data-file",name);
					            		$("a.btn-rename[data-hash='"+hash+"']").attr("data-fullpath",response);
					            		$("a.btn-content[data-hash='"+hash+"']").attr("data-file",name);
					            		$("a.btn-content[data-hash='"+hash+"']").attr("data-fullpath",response);
					            		$("a.btn-download[data-hash='"+hash+"']").attr("data-file",name);
					            		$("a.btn-download[data-hash='"+hash+"']").attr("data-fullpath",response);
					            		$("a.btn-delete[data-hash='"+hash+"']").attr("data-file",name);
					            		$("a.btn-delete[data-hash='"+hash+"']").attr("data-fullpath",response);
					            	} 
					            	if(type == 1){
					            		$("a.btn-rename[data-hash='"+hash+"']").attr("data-file",name);
					            		$("a.btn-rename[data-hash='"+hash+"']").attr("data-fullpath",response)
					            		$("tr[id='"+hash+"'] a.btn-dir").attr("data-path", response);
					            	}
				            		
				            	}
				           }
				       });
					}

				});

				$(document).on("click", ".btn-delete", function(e){
					e.preventDefault();
					$("#modal-delete").modal('show');
					file = $(this).attr("data-file");
					fullpath = $(this).attr("data-fullpath");
					hash = $(this).attr("data-hash");
					$("#modal-delete-filename").html(file);
				});

				$(document).on("click", "#btn-delete-file", function(e){
					e.preventDefault();

					$.ajax({
			            type: "POST",
			            url: 'index.php',
			            data: {
			            	action: "DEL",
			            	file: fullpath,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            success: function(response)
			            {
			                if(response == "1"){
			                	$("#modal-delete").modal('hide');
			                	$("tr#"+hash).remove();
			                }
			 
			           }
			       });

				});


				$(document).on("click", ".btn-delete-folder", function(e){
					e.preventDefault();
					$("#modal-delete-folder").modal('show');
					file = $(this).attr("data-file");
					fullpath = $(this).attr("data-fullpath");
					hash = $(this).attr("data-hash");
					$("#cannot-delete-folder").hide();
					$("#modal-delete-foldername").html(file);
				});

				$(document).on("click", "#btn-delete-folder", function(e){
					e.preventDefault();

					$.ajax({
			            type: "POST",
			            url: 'index.php',
			            data: {
			            	action: "RMDIR",
			            	fullpath: fullpath,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            success: function(response)
			            {
			            	console.log(response)
			                if(response == "1"){
			                	$("#modal-delete-folder").modal('hide');
			                	$("tr#"+hash).remove();
			                } else {
			                	$("#cannot-delete-folder").show();
			                }
			 
			           }
			       });

				});

				
				

				//$(".btn-dir").on("click", function(e){
				$(document).on("click", ".btn-dir", function(e){
					e.preventDefault();					
					var p = $(this).attr("data-path");
					path = p;

					$.ajax({
			            type: "POST",
			            url: 'index.php',
			            data: {
			            	action:"OPENDIR",
			            	path: path,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function(){
			            	$("#file-explorer").hide();
							$("#loading-content").show();
			            },
			            success: function(response)
			            {
			            	$("#loading-content").hide();
			            	$("#file-explorer").html(response).show();			            	
			            }
			       });

				});

				var load = function(){
					$.ajax({
			            type: "POST",
			            url: 'index.php',
			            data: {
			            	action:"OPENDIR",
			            	path: path,
			            	host: "<?php echo $host;?>",
			            	port: <?php echo $port;?>,
							protocol: "<?php echo $protocol;?>"
			            },
			            beforeSend: function(){
			            	$("#file-explorer").hide();
							$("#loading-content").show();
			            },
			            success: function(response)
			            {
			            	$("#loading-content").hide();
			            	$("#file-explorer").html(response).show();			            	
			            }
			        });

				};

				<?php if($connected) { ?>
					load();
				<?php } ?>

			});
		</script>		
  </head>
  <body class="h-100">
    <div class="container d-flex flex-column h-100" style="padding-top:15px">
    	<div class="flex-grow-1">

	  		<div class="row d-none d-md-block">
				<div class="col-md-12">    
					<div class="bblack white" style="padding:0 20px;text-align:left;font-size:100px">PHP-TNFS</div>
				</div>
			</div>

			<div class="jumbotron" style="display:none">
				<h1>Simple PHP TNFS Client</h1>
				Simple PHP TNFS Client
			</div>

			<p></p>
			
			<div class="row">
				<div class="col-md-12">    
					
					<form class="form-row" method="post"  autocomplete="off">
							<input type="hidden" name="sid" value="<?php echo $sid;?>"/>
							<div class="col-md-4">
								<!--
									<input type="text" <?php if($tnfs != null && $tnfs->CONNECTED == true) echo " disabled ";?> class="form-control mb-2 mr-sm-2" value="<?php echo $host;?>" name="host" placeholder="tnfs host">                                  										
								-->
								<div id="dropdown" class="dropdown" style="border:none">
								  <button style="text-align:left;" class="form-control btn btn-secondary dropdown-toggle <?php if($tnfs != null && $tnfs->CONNECTED == true) echo " disabled ";?>" type="button" id="dropdown-hosts" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    <?php echo $host;?>
								  </button>
								  <div id="dropdown-hosts-list" style="margin-top:-8px" class="form-control dropdown-menu" aria-labelledby="dropdown-hosts">
								    <input autocomplete="off" style="height:auto;" type="text" <?php if($tnfs != null && $tnfs->CONNECTED == true) echo " disabled ";?> class="form-control mr-sm-2" value="<?php echo $host;?>" name="host" id="host" placeholder="tnfs host">                                  																		    
								    <span href="#" style="background-color:#fff; padding:5px 0px" class="dropdown-item"><hr style="margin:0;border-top:2px solid #000"/></span>
									<?php foreach(TNFS_HOSTS as $host): ?>
										<a class="dropdown-item host-element" href="#"><?php echo $host ?></a>
									<?php endforeach ?>
								  </div>
								</div>
							</div>
							<div class="col-md-1">
								<div id="dropdown-protocol-outer" class="dropdown" style="border:none">
								  <button style="text-align:left;" class="form-control btn btn-secondary dropdown-toggle <?php if($tnfs != null && $tnfs->CONNECTED == true) echo " disabled ";?>" type="button" id="dropdown-protocol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    <?php echo $protocol;?>
								  </button>
								  <div id="dropdown-protocol-list" style="margin-top:-8px" class="form-control dropdown-menu" aria-labelledby="dropdown-protocol">
								    <input autocomplete="off" style="height:auto;" type="text" <?php if($tnfs != null && $tnfs->CONNECTED == true) echo " disabled ";?> class="form-control mr-sm-2" value="<?php echo $protocol;?>" name="protocol" id="protocol" placeholder="tnfs protocol">                                  																		    
								    <span href="#" style="background-color:#fff; padding:5px 0px" class="dropdown-item"><hr style="margin:0;border-top:2px solid #000"/></span>
								    <a class="dropdown-item protocol-element" href="#">tcp</a>
								    <a class="dropdown-item protocol-element" href="#">udp</a>							    								    
								  </div>
								</div>
							</div>
							<div class="col-md-1">
									<input type="text" <?php if($tnfs != null && $tnfs->CONNECTED == true) echo " disabled ";?> class="form-control mb-2 mr-sm-2" value="<?php echo $port;?>" name="port" id="port" placeholder="port">                                  
							</div>
							<div class="col-md-3">
								<?php if(!$connected){ ?>
								<button class="form-control mb-2 btn btn-small bcyan black" id="btn-connect" name="connect" type="submit">Connect</button> 
								<?php } else { ?>
								<button class="form-control mb-2 btn btn-small bcyan black" id="btn-disconnect" name="disconnect" type="submit">Disconnect</button> 
								<?php } ?>
							</div>
							<div class="col-md-3">
								<div id="info-connecting" class="form-control mb-2 alert bblue white" style="display:none;padding:2px 10px">Connecting...</div>
								<div id="info-disconnecting" class="form-control mb-2 alert bblue white" style="display:none;padding:2px 10px">Disconnecting...</div>
								<?php if($connected){ ?>
									<div id="info-connected" class="form-control mb-2 alert  bgreen black" style="padding:2px 10px">Connected!!!</div>
								<?php } ?>
								<?php if($tnfs != null && $tnfs->CONNECTED == false){ ?>
									<div id="info-connect-error" class="form-control mb-2 alert bred white" style="display:block;padding:2px 10px">Error!!!</div>
								<?php } ?>
								<?php if($tnfs == null ){ ?>
									<div id="info-connect-not" class="form-control mb-2 alert bgray black bright0" style="display:block;padding:2px 10px">Not connected</div>
								<?php } ?>
							</div>
					</form>
				</div>
			</div>

			<?php if($disconnect == false && $connected){ ?>
			<div class="row">
				<div class="col-md-12" id="file-explorer">
				</div>
			</div>
			<div class="row" id="loading-content" style="margin-top:10px;display: none">
				<div class="col-md-12">
					<div class="alert alert-info">Loading content... please wait...</div>
					<!--<progress></progress>-->
				</div>
			</div>
			<?php } ?>
		</div>
		<footer>
			<p><span class="blink">fastofruto 2019</span></p>
		</footer>
	</div>

	

	<div id="modal-download" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Download file</h4>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>Downloading file from TNFS server, please wait...</p>
	        <p id="modal-download-filename" class="alert byellow black"></p>
	        <p>The download will start automatically</p>
	      </div>
	      <div class="modal-footer">
	      	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	      </div>
	    </div>
	  </div>
	</div>


	<div id="modal-delete" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Delete file</h4>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>Are you sure you want to delete this file?</p>
	        <p id="modal-delete-filename" class="alert byellow black"></p>
	      </div>
	      <div class="modal-footer">
	        <button id="btn-delete-file" type="button" data-file="" class="btn bred white">Yes! Delete it!</button>
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div id="modal-delete-folder" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Delete folder</h4>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>Are you sure you want to delete this folder?</p>
	        <p id="modal-delete-foldername" class="alert byellow black"></p>
	        <p class="alert alert-danger bred white" id="cannot-delete-folder" style="display: none">ERROR: Cannot delete folder<br/>Folder not empty or not enough permissions.</p>
	      </div>
	      <div class="modal-footer">
	        <button id="btn-delete-folder" type="button" data-file="" class="btn bred white">Yes! Delete it!</button>
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div id="modal-move" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Move file</h4>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>Move file to:</p>
	        <input type="text" class="form-control byellow black" id="move-to" value="" />
	      </div>
	      <div class="modal-footer">
	        <button id="btn-move-file" type="button" data-file="" class="btn bblue white">Move</button>
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div id="modal-new-folder" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">New folder</h4>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>Folder name:</p>
	        <input type="text" maxlength="20" class="form-control byellow black" id="new-folder-name" value="" />
	      </div>
	      <div class="modal-footer">
	        <button id="btn-create-folder" type="button" data-file="" class="btn bblue white">Create</button>
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div id="modal-rename" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Rename file</h4>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>Write the new name for the file: <span id="old-file"></span></p>
	        <input type="hidden" id="rename-file" value=""/>
	        <input type="text" class="form-control byellow black" autofocus id="rename-filename"/>
	      </div>
	      <div class="modal-footer">
	        <button id="btn-rename-file" type="button" data-file="" class="btn bblue white">Rename</button>
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div id="modal-content" class="modal " tabindex="-1" role="dialog">
	  <div class="modal-dialog modal-lg" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title" id="file-name">View file content</h4>	        
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span class="btn x-close bblack white" aria-hidden="true">X</span>
	        </button>
	      </div>
	      <div class="modal-body">
	      	<div id="file-content" style="word-break: break-word;"></div>	        
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	      </div>
	    </div>
	  </div>
	</div>

<style type="text/css">
	img.scr {
	   image-rendering: pixelated;
	}
	</style>

  </body>
</html>