<?php

class codetree{

	var $main_content = '';
	var $classname, $extended, $scope, $props, $methods;
	var $output;

	function __construct( $content_class, $CLASS ){
		
		$content = $this->prepare( $content_class, $CLASS );
		$this->main_content = $content;

		// Separa em partes princpais
		preg_match('/class\s([\w]+)\s(extends\s([\w]+)\s)*\{(.*)\}/i', $content, $match);

		$this->classname = trim($match[1]);
		$this->extended = trim($match[3]);
		$this->scope = trim($match[4]);

		// pega propriedades
		preg_match_all('/(protected|public|private|var)\s\$[\w]+.*?\;/i', $this->scope, $match);
		if( count($match) ){
			$this->props = $match[0];
		}

		// pega metodos
		preg_match_all('/(protected|public|private|static)\sfunction\s([\w]+)\s*\((.*?)\)\s*/i', $this->scope, $match_methods);
		if( count($match_methods) ){
			// pega escopos dos metodos
			foreach( $match_methods[0] as $i => $met ){

				$tmp = trim( preg_replace('/(.*)'. $this->addslashe($met) .'(.*)/i', '$2', $this->scope) );
				$scope = trim(preg_replace('/(^{)(.*?)(}$)/i', '$2', $this->until_the_end( $tmp, '{', '}' ) ) );

				$this->methods[] = array( 	'id' 		=> $i,
											'text' 		=> trim($match_methods[2][$i]),
											'icon'		=> 'glyphicon glyphicon glyphicon-play-circle',
											'state'		=> array('opened' => false, 'selected' => false),
											'scope'		=> $scope,
											'children'	=> $this->flow( $scope ) );

				/*$this->methods[trim($match_methods[2][$i])] = array(	'called' 	=> trim($met),
																		'type'		=> trim($match_methods[1][$i]),
																		'param'		=> trim($match_methods[3][$i]),
																		'scope'		=> trim(preg_replace('/(^{)(.*?)(}$)/i', '$2', $this->until_the_end( $tmp, '{', '}' ) ) ) );*/

			}
		}

		
		//echo "<pre>".print_r( $this->methods['BuscaTabelaValores'], 1 )."</pre>";
		$this->output = json_encode($this->methods,1);
			
	}

	function flow( $scope ){
		
		$tmp = $scope;
		$children = '';

		while(preg_match( '/^\@*(\$(this->)*[\w]+|[\w]+)(.*)/i' , $tmp, $match)){

			$class = '';
			$init = trim($match[1]);
			$text = $init;
			$rest = $match[3];	
			$sub = '';		
			
			$pv = strpos( $rest, ';' );
			$prev = substr( $rest, 0, $pv+1 ); 
			$tmp = trim(substr( $rest, $pv+1 )); 

			$condition = '';
			$command = '';
			$setvalue = '';

			if( $init[0] == '$'){ // variable
				
				preg_match( '/^(.*)\;/i' , trim($prev), $_m);
				if( count( $_m ) ){
					$setvalue = $_m[1];
				}

				/*$children[] = array( 	'text' 	=> $setvalue,
									 	'icon'	=> 'glyphicon glyphicon-log-in',
									 	'state'	=> array('opened' => true, 'selected' => true) );*/
				$class = 'glyphicon glyphicon-flag';
				$text .= $setvalue;


			} else { 

				switch( $init ){

					case 'return':

						$text = $init.$rest;

						break;

					default:

						$class = 'glyphicon glyphicon-random'; 

						if( strpos(' '.$prev,'{') or
						 	strpos(' '.$prev,'}') ){ // Se houver abertura e fechamento de chaves

							$tmp = $prev.$tmp;

							$spv = preg_replace('/(.*?)({.*)/i','$2', $tmp );

							preg_match( '/\((.*?[\(\)]*)\)\s*{/i' , $tmp, $_m);
							if( count( $_m ) ){
								$condition = $_m[1];
								$command = $this->until_the_end( $spv, '{', '}' );
								$partner = '(.*?)'.$this->addslashe($command).'(.*)';
								$tmp = trim( preg_replace('/'.$partner.'/i', '$2', $tmp) );	
								//echo $partner."<BR>";

							}
							//echo $tmp;
							$sub = $this->flow( $this->clear_edge( $command ) );

						} else { // Se não tiver

							//echo $pv[0]."<BR>";
							preg_match( '/\((.*?[\(\)\s]*[^(]+)\)(.*)/i' , $prev, $_m);
							if( count( $_m ) ){
								$condition = $_m[1];
								$command = $_m[2];	
							}

							$sub[] 	 = array( 	'text' 	=> $command,
												'icon'	=> 'glyphicon glyphicon-chevron-right',
												//'state'	=> array('opened' => true, 'selected' => true) 
												);

							
						}

						//echo $this->clear_edge($command)."<BR><BR>";

						$text .= "( ".$condition." ) ";

						break;

				}
				
			}

			$children[]	= 	 array(	'text' 		=> $text,
									'icon'		=> $init.' '.$class,
									/*
									'condition' => $condition,
									'scope'		=> $command,
									'setvalue'	=> $setvalue,
									*/
									'children'	=> $sub,
									'state'	=> array('opened' => true, 'selected' => false) );

			

		}

		return $children;
		
	}

	function clear_edge( $str ){
		$str = trim($str);
		$str = preg_replace( '/^{(.*)\}$/i','$1', $str );
		$str = trim($str);
		return $str;		
	}

	function addslashe( $str ){
		return addcslashes($str, "/()$.+&|'*![]");
	}

	function prepare( $content, $CLASS ){

		//limpa comentarios
		$content = preg_replace('/\/\/.*?[\n|\r]/i', '', $content);

		// limpar espaços duplos
		$content = preg_replace('/\s+/', ' ', $content);
		$content = preg_replace('/}\s+{/', '}{', $content);

		$content = preg_replace('/\/\*.*?\*\//i', '', $content);

		$content = preg_replace('/\".*?\"/i', '#ASPAS', $content);

		// limpa espaço inicial
		$content = preg_replace('/^\s+/', '', $content);
		$content = trim($content);


		$content = preg_match('/(.*)(class\s'.$CLASS.'.*?)({.*)/i', $content, $m );

		$class_call = $m[2];
		$scope = trim($m[3]);	
		$scope = $this->until_the_end( $scope, '{', '}' );

		$content = $class_call.$scope;
		
		return $content;
	}


	function until_the_end( $str, $open, $close ){

		$tmp = '';
		$parts = explode($close,$str);
		foreach( $parts as $p ){
			$tmp .= $p.$close;
			if( ( substr_count( $tmp, $open ) == substr_count( $tmp, $close ) ) ){
				break;
			}
		}
		
		return $tmp;
	}

	
}

?>