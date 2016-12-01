<?php

include('codetree.class.php');

$file = '';
$class = '';
$json = '';
if( @$_POST ){
	$file = $_POST['file'];
	$class = $_POST['class'];
	$codetree = new codetree( file_get_contents( $file ), $class );
	$json = $codetree->output;
}

//echo "<PRE>".print_r( $codetree->methods , 1 )."</PRE>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>jstree basic demos</title>
	<style>
	*{ 	font-family: Arial;
		font-size: 12px; }
	html { margin:0; padding:0; font-size:62.5%; }
	body { max-width:100%; margin:0 auto; padding:20px 10px; font-size:14px; font-size:1.4em; }
	h1 { font-size:1.8em; }
	.demo { border:1px solid silver; min-height:100px; }
	</style>
	<link rel="stylesheet" href="dist/themes/default/style.min.css" />
	<link rel="stylesheet" href="css/bootstrap.min.css" />
</head>
<body>
	<div>
		<div class="row">
			<div class="col-md-3"><h2>Code Tree</h2></div>
		</div>	
		<div class="row">
			<form method="post">
			<div class="col-md-6">
				<div class="input-group">
				 	<span class="input-group-addon" id="sizing-addon2"></span>
					<input type="text" class="form-control" placeholder="File" aria-describedby="sizing-addon2" name="file" value="<?=$file?>">
				</div>
			</div>
			<div class="col-md-3">
				<div class="input-group">
					<span class="input-group-addon" id="sizing-addon2"></span>
				 	<input type="text" class="form-control" placeholder="Class" aria-describedby="basic-addon1" name="class" value="<?=$class?>">
				</div>
			</div>
			<div class="col-md-3">
				<div class="btn-group" role="group" aria-label="...">
				  <button type="submit" class="btn btn-default">Search</button>
				</div>
			</div>
			</form>
		</div>	
		<div style="height:20px"></div>

		<div class="row">
			<div class="col-md-12">

				<div id="html" class="demo"></div>

			</div>
		</div>	

	</div>



	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<? if($json) { ?> 
	<script src="dist/jstree.min.js"></script>	
	<script>

	$(function(){
		var to = false;
		  $('#search').keyup(function () {
		    if(to) { clearTimeout(to); }
		    to = setTimeout(function () {
		      var v = $('#search').val();
		      $('#html').jstree(true).search(v);
		    }, 250);
		  });

		  // html demo
			$('#html').jstree({"plugins" : [ "search" ],
				'core' : {
					'data' : <?=$json?>,
					"types" : {
				      "default" : {
				        "icon" : "glyphicon glyphicon-flash"
				      },
				      "demo" : {
				        "icon" : "glyphicon glyphicon-ok"
				      }
				    }
				}
			});

	})
	<? } ?> 

	
	</script>
</body>
</html>

