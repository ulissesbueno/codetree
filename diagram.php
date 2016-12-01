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
<html>
<head>
	<meta charset="UTF-8">
	<title>jstree basic demos</title>
	<style>
	*{ 	font-family: Arial;
		font-size: 12px; }
	html { margin:0; padding:0; font-size:62.5%; }
	body { max-width:100%; margin:0 auto; padding:20px 10px; font-size:14px; font-size:1.4em; }
	h1 { font-size:1.8em; }
	.main{
		height: 100%;
	}
	#diagram{
		
	}
	
	ul{
		list-style: none;
		padding: 0px;
		margin: 0px;
	}

	</style>
	<link rel="stylesheet" href="css/bootstrap.min.css" />
</head>
<body>
	<div class="container-fluid main">
		<div class="row">
			<div class="col-md-3"><h2>Code Tree</h2></div>
		</div>	
		<div class="row">
			<div class="col-md-12">

				<div class="row">
					<div class="col-md-2 well ">
						<ul id='variables'>
						</ul>
					</div>
					<div class="col-md-8" id='center'>
						<canvas id="diagram"  height='800' >
							
						</canvas>
					</div>
					<div class="col-md-2 well">
						
					</div>
				</div>

			</div>
		</div>	
	</div>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<? //if($json) { ?> 
	<script type="text/javascript" src='js/jcanvas.min.js'></script>

	<script>

	// symblos 
	var symbol_size = 2;
	var symbols_ = { 'default' 	: {	fillStyle: 'steelblue',
									strokeStyle: '#333',
									strokeWidth: 0,
									x: 0, y: 0,
									radius: 4 },
					'condition' : { strokeStyle: 'steelblue',
								 	fillStyle: 'steelblue',
								  	strokeWidth: 0,
								  	x: 200, y: 100,
								  	radius: 6,
								  	sides: 4,
								  	rotate: 45
								},
					'loop'		: { strokeStyle: 'steelblue',
									strokeWidth: 2,
									x: 100, y: 100,
									radius: 5,
									start: 0, end: 300 } }
	var flow = [{ 	'piece' : 'condition',
					'interc': [{ 'var' 	 : '$Idade'  }],
					'affect': [{ 'var' 	 : '$Result' }] }
				];

	var maxw = $('#center').width();
	var maxh = $('#center').height();

	var time = flow.length + 1;
	var timebar = ( maxw / time);
	var height_line = 35;
	var dis_top = $('#variables').position().top;
	
	
	$('#diagram').prop('width',maxw);
	$('#diagram').prop('height',maxh);

	// variables
	var variables = [{	'name' : '$Nome', 	'state' : 'normal'},
					 {	'name' : '$Idade', 	'state' : 'normal'},
					 {	'name' : '$Result',	'state' : 'normal'}];

	$(function(){

		menu()
		drawDiagram();

	})

	function menu(){
		$('#variables').html('')
		for( var i in variables ){
			$('#variables').append("<li><input class='form-control' name='"+ variables[i].name +"' placeholder='"+ variables[i].name +"' /></li>");
		}
		$('#variables li').css(	{'height'		: height_line+"px",
								 'line-height' 	: height_line+"px" });
	}

	function drawDiagram(){

		flow.unshift( {'piece' : 'init'} );
		flow.push( {'piece' : 'end'} );

		var posline;
		var diagram = $('#diagram');
		var flow_px = 0;
		var var_px = 0;
		var distance_lines = 7;
		var symbols = '';

		var x = 0;
		// Desenha o flow... linhas verticais
		for( var i in flow ){

			symbols = '';
			symbols = symbols_;

			flow_px = x ;
			if( x > 0 ) flow_px = x * timebar;
			//var_px = ( ((x+1) * timebar) - ( timebar / 2));

			diagram.drawLine({
				strokeStyle: '#ccc',
				strokeWidth: 1,
				rounded: true,
				closed: true,
				x1: flow_px, y1: 0,
				x2: flow_px, y2: maxh
			})
			
			switch( flow[i].piece ){
				case 'condition':
						symbols.condition.x = flow_px;
						symbols.condition.y = symbols.condition.radius
						diagram.drawPolygon(symbols.condition);
					break;

				case 'loop':
						symbols.loop.x = flow_px;
						symbols.loop.y = symbols.loop.radius
						diagram.drawArc(symbols.loop);
					break;

				default :

						symbols.default.x = flow_px;
						symbols.default.y = symbols.default.radius
						diagram.drawArc(symbols.default);

					break;
			}

			var interc = flow[i].interc;				
			var affect = flow[i].affect;
			var y = 1;
	
			for( var v in variables ){
				var midlle = ( (y * height_line) - ( height_line / 2) ) + dis_top;
				var py1 = midlle - distance_lines;
				var py2 = midlle + distance_lines;

				if(variables[v].state == 'normal'){
					diagram.drawLine({ // line true
						strokeStyle: '#999',
						strokeWidth: 1,
						rounded: true,
						closed: true,
						x1: x, y1: midlle,
						x2: flow_px, y2: midlle
					})
					variables[v].state = 'conditional';
				} else{
					diagram.drawLine({ // line true
						strokeStyle: 'steelblue',
						strokeWidth: 1,
						rounded: true,
						closed: true,
						x1: x, y1: py1,
						x2: flow_px, y2: py1
					}).drawLine({ // line false
						strokeStyle: 'red',
						strokeWidth: 1,
						rounded: true,
						closed: true,
						x1: x, y1: py2,
						x2: flow_px, y2: py2
					})
				}



				/*for( var i in interc ){

					if( interc[i].var == variables[v].name ){							
						symbols.condition.x = px;
						symbols.condition.y = midlle

						diagram.drawPolygon(symbols.condition)
						.drawPath({ 
							strokeStyle: 'green',
							strokeWidth: 1,
							p1: {
							    type: 'line',
							    x1: px, y1: midlle,
							    x2: px+(timebar/2), y2: midlle
							  },
							p2: {
							    type: 'line',
							    x1: px+(timebar/2), y1: midlle,
							    x2: px+(timebar/2), y2: dis_top
							  },
							p3: {
							    type: 'line',
							    x1: px+(timebar/2), y1: midlle,
							    x2: px+(timebar/2), y2: maxh
							  }

						})
						break;
					}
				}

				for( var a in affect ){
					if( affect[a].var == variables[v].name ){
						symbols.default.x = px+(timebar/2);
						symbols.default.y = py1 ;
						diagram.drawArc(symbols.default);

						symbols.default.x = px+(timebar/2);
						symbols.default.y = py2;
						diagram.drawArc(symbols.default);

					}
				}	*/			

				y++;
			}

			x++ ; 
		}

		

	}

	<? //} ?> 

	
	</script>
</body>
</html>

