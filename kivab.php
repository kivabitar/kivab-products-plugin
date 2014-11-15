<?php
	/*
	Plugin Name: Sonetics by Kiva Bitar
	Based off Jesper Angelo noshop. Heavily altered. 
	Orginal authorship below -

	Description: kivab. Allows you to put a list of items on a structured list with pictures, replacing the need for a real shopping cart.
	Version: 1.1
	Author: Kiva Bitar
	Author URI: http://www.kivab.com
	License: GPL2
	*/
	/*  Copyright 2012 Kiva Bitar  (email : kiva.bitar@kivabcorp.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License, version 2, as
		published by the Free Software Foundation.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	global $wpdb;

	if ( ! function_exists( 'is_ssl' ) ) {
		function is_ssl() {
			if ( isset($_SERVER['HTTPS']) ) {
				if ( 'on' == strtolower($_SERVER['HTTPS']) )
					return true;
				if ( '1' == $_SERVER['HTTPS'] )
					return true;
			} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
				return true;
			}
			return false;
		}
	}

	if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
		$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) ) . "HEY";
	} else {
		$wp_content_url = get_option( 'siteurl' ) . "DUDE";
	}
	$wp_content_url = '/wp-content';
	$wp_content_dir = ABSPATH . 'wp-content';
	$wp_plugin_url = $wp_content_url . '/plugins';
	$wp_plugin_dir = $wp_content_dir . '/plugins';
	$wpmu_plugin_url = $wp_content_url . '/mu-plugins';
	$wpmu_plugin_dir = $wp_content_dir . '/mu-plugins';

	$wp_kivab_url = $wp_plugin_url . '/kivab';
	$wp_kivab_dir = $wp_plugin_dir . '/kivab';
	$wpmu_kivab_url = $wpmu_plugin_url . '/kivab';
	$wpmu_kivab_dir = $wpmu_plugin_dir . '/kivab';

	$wpdb->show_errors();

	register_activation_hook(__FILE__,'kivab::kivab_activate');

	global $kivab_db_version;
	$kivab_db_version = "0.5.3";

	if (!class_exists("kivab")) {

		class kivab {
			//constructor
			function kivab()
			{
				// Hook up Actions and Filters
				//if (isset($kivab_plugin)) {
					//Actions
					//add_action('wp_head', 'kivab::CSS');
					add_action('wp_footer', 'kivab::Products');

					//Filters
					add_filter( 'the_content', 'kivab::ShowTable' );
					add_action('admin_menu', 'kivab::PluginMenu');
				//}
			}

			// Install part: Create table for products (or check if it's there at least)
			public static function kivab_activate() {
				global $wpdb;
				global $kivab_db_version;

				// First the Product Table
				$table_name = $wpdb->prefix . "kivab_products";
//				if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
				//finish sql clean up
					$sql = "CREATE TABLE `" . $table_name . "` (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						time bigint(11) DEFAULT '0' NOT NULL,
						category tinytext NOT NULL,
						title tinytext NOT NULL,
						description text NOT NULL,
						url VARCHAR(250) NOT NULL,
						imgurl VARCHAR(250) NOT NULL,
						imgurlmode VARCHAR(1) NOT NULL,
						isActive VARCHAR(1) DEFAULT '1' NOT NULL,
						prodOrder INT(3) DEFAULT '10',
						UNIQUE KEY  id (id),
						PRIMARY KEY  primarykey (id)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
					//print_r($dbDelta);
					//die("stop");
//					echo "<h1>Table Updated<h1>";
/*					$rows_affected = $wpdb->insert( $table_name, array(
												'time' => current_time('mysql'),
												'category' => 'Stuff',
												'title' => 'Tin-Tin',
												'description' => 'Full-size Tin-Tin figure in molded plastic!',
												'url' => 'http://tintin.org',
												'imgurl' => 'http://tintin.org/logo.png',
												'imgurlmode' => ''
												) );
*/
//				} // End if()

				// Second some Product Specs
				$table_name = $wpdb->prefix . "kivab_product_specs";
//				if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
					$sql = "CREATE TABLE `" . $table_name . "` (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						product_id mediumint(9) NOT NULL,
						time bigint(11) DEFAULT '0' NOT NULL,
						spectitle tinytext NOT NULL,
						specvalue text NOT NULL,
						specOrder INT(3) DEFAULT '10',
						UNIQUE KEY  id (id)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
/*					$rows_affected = $wpdb->insert( $table_name, array(
						'product_id' => 1,
						'time' => current_time('mysql'),
						'spectitle' => 'Plastics',
						'specvalue' => 'Good solid plastics.'
					) ); */
//				} // End if()
					$table_name = $wpdb->prefix . "kivab_images";
						
					$sql = "CREATE TABLE `" . $table_name . "` (
					imgID mediumint(9) NOT NULL AUTO_INCREMENT,
					productID int NOT NULL,
					imgPath VARCHAR(250) NULL,
					imgAlt VARCHAR(250) NULL,
					imgOrder INT(3) DEFAULT '10',
					UNIQUE KEY  id (imgID),
					PRIMARY KEY  primarykey (imgID)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);

					$table_name = $wpdb->prefix . "kivab_documents";
					
					$sql = "CREATE TABLE `" . $table_name . "` (
					documentID mediumint(9) NOT NULL AUTO_INCREMENT,
					productID int NOT NULL,
					documentURL VARCHAR(250) NULL,
					documentTitle VARCHAR(250) NULL,
					docOrder INT(3) DEFAULT '10',
					UNIQUE KEY  id (documentID),
					PRIMARY KEY  primarykey (documentID)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
					
					
					$table_name = $wpdb->prefix . "kivab_categories";
					
					$sql = "CREATE TABLE `" . $table_name . "` (
					catID mediumint(9) NOT NULL AUTO_INCREMENT,
					categoryName tinytext NOT NULL,
					categoryDesc text NULL,
					categoryImg VARCHAR(250) NULL,
					sortOrder INT(3) DEFAULT '10',
					UNIQUE KEY  id (catID),
					PRIMARY KEY  primarykey (catID)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
						
				add_option("kivab_db_version", $kivab_db_version);
			} // End kivab_activate()

			// This just echoes the chosen line, we'll position it later
			public static function Products() {
				global $wpdb;
				//$wpdb->print_error();
			} // End Products()


			// Now we set that function up to execute when the admin_footer action is called

			// We need some CSS to position the paragraph
			public static function CSS() {
				global $wp_kivab_url;
				global $wpmu_kivab_url;

			  // This makes sure that the posinioning is also good for right-to-left languages
	//          $x = ( is_rtl() ) ? 'left' : 'right';
				echo "<link rel=\"stylesheet\" href=\"".$wp_kivab_url."/kivab.css\" >\n";
				echo "<!-- ".$wp_kivab_url." -->\n";
				echo "<!-- ".$wpmu_kivab_url." -->\n";

			} // End CSS()

			public static function ShowTable($content) {
				$cat = "";
				$search = "@\s*\[kivab ([^\]]+)\]\s*@i";
				$myNumb = substr($content, -4); 
				if(preg_match_all($search, $content, $matches)) {
					if(is_array($matches)) {
						foreach($matches[1] as $cat) {
							// Get the data from the tag
							//print("\n<br />\n");
							//print_r($cat);
							//$content = str_replace ($search, $replace, $content);
							$content = preg_replace( "$search", kivab::createtable($cat), $content );
							//$content .= "<h2>THAT WAS CATEGORY: ".$cat."</h2>\n";
						}
					}	
				}

				//$content = str_replace( "[kivab]", "THIS?", $content );
				return $content;
			} // End ShowTable($content)
			
			
			public function createtable($cat) {
				// get options
				global $wp_kivab_url;
				global $wp_kivab_dir;
				global $wpmu_kivab_url;
				global $wpmu_kivab_dir;
				global $content;
				
				$getDash = strpos($cat,'-');
				
				if($getDash){
					$mySpecNumb = substr($cat,$getDash  + 1);
					//echo '<h1>Hellz yeah!'.$mySpecNumb .'</h1>';
					$myProd = $mySpecNumb;
					$safeProd = $myProd;
				}
				else{
				$myProd = $_GET['id'];
				$myCat = mysql_real_escape_string($_GET['catID']);
				$safeProd = mysql_real_escape_string($myProd);
				}
				$myURL = $_SERVER["REQUEST_URI"];
				
				
				if ($safeProd == null || $safeProd == ""){
					//subcategory level display
					$options = get_option('kivab_options');
					if($wptouch)	$width=$options['wptouchwidth'];
					else			$width=$options['width'];
						if ($cat == 0) {$full = true;};
					
						if($cat != 0 || $myCat != ""){
							if ($myCat !=0 || $myCat != null){
								$cat = $myCat;
								
								$myURL = $_SERVER["REQUEST_URI"];
								$fixed_url = substr($myURL, 0, strpos($myURL, '?'));
							}
							else{
								$myURL = $_SERVER["REQUEST_URI"];
								$fixed_url = $myURL;
							}
						$widthparam = " width=".$width." style=\"width:".$width."px;\" ";
						
						
						
						//sub-category level display
						global $table_prefix, $wpdb;
						$table_name = $wpdb->prefix . "kivab_products";
						
						if($cat == 88){
							
							//gen 2 legacy products - Hard coded
							$sql = $wpdb->prepare( "SELECT * FROM $table_name INNER JOIN wp_kivab_categories ON wp_kivab_categories.catID = wp_kivab_products.category
									WHERE wp_kivab_categories.generation=%s AND wp_kivab_categories.sortOrder != 99 AND wp_kivab_products.isActive != 0 ORDER BY wp_kivab_categories.sortOrder ASC, wp_kivab_products.prodOrder ASC, wp_kivab_products.title", 2 );
									$return = '';
								
							$myrows = $wpdb->get_results( $sql );
							$return = '<div class="category-filters">
							<ul>
								<li><h2>Filters</h2></li>
								<li><input type="checkbox" name="chk[]" class="checkfilter" data-catID="6"  value="6"  CHECKED>Complete Wireless Systems</li>
								<li><input type="checkbox" name="chk[]" class="checkfilter" data-catID="1" value="1" CHECKED>Wireless Headsets</li>
								<li><input type="checkbox" name="chk[]" class="checkfilter" data-catID="2"  value="2" >Intercoms</li>
								<li><input type="checkbox" name="chk[]" class="checkfilter" data-catID="4"  value="4"  >Base Stations</li>
								<li><input type="checkbox" name="chk[]" class="checkfilter" data-catID="11"  value="11"  >Wireless Belt Packs and Stealth Headsets</li>
								<li><input type="checkbox" name="chk[]" class="checkfilter" data-catID="9"  value="9"  >Direct Wire</li>
							</ul>

							</div><div class="kivab-categories">';
							foreach ($myrows as $myrows) {
								$pathOfPage = get_bloginfo('wpurl') . '/legacy-products/' . strtolower(str_replace(" ", "-", $myrows->categoryName)) . '/' . strtolower(str_replace("/","-",str_replace(" ", "-", $myrows->title))).'/';
								$pathOfPage = str_replace("&", "", $pathOfPage);
								$pathOfPage = str_replace("---", "-", $pathOfPage);
								$pathOfPage = str_replace("--", "-", $pathOfPage);
							
								$ret = file_get_contents( $wp_kivab_dir . '/categoryprod.template.xhtml' );
								//$ret = str_replace( '{url}', get_bloginfo('wpurl') . $fixed_url . '?id=' . $myrows->id . '&amp;product-name='. urlencode($myrows->title) . '&amp;category-name=' . urlencode($myrows->categoryName), $ret );
								$ret = str_replace( '{catID}', $myrows->catID, $ret);
								$ret = str_replace( '{produrl}', $pathOfPage, $ret );
								$ret = str_replace( '{prodimg}', ( $myrows->imgurl ? $myrows->imgurl : $options['defimg'] ), $ret );
								$ret = str_replace( '{prodname}', $myrows->title, $ret );
								$return .= "\n\n". $ret . "\n";
							} // End foreach
							$return .= '</div>';
							$return .= file_get_contents( $wp_kivab_dir . '/categoryscript.template.xhtml' );
							return $return;
						}//end gen2 legacy products
						
						if($cat<>"")
								$sql = $wpdb->prepare( "SELECT * FROM $table_name INNER JOIN wp_kivab_categories ON wp_kivab_categories.catID = wp_kivab_products.category
										 WHERE wp_kivab_products.category=%s  AND wp_kivab_products.isActive != 0 ORDER BY wp_kivab_products.prodOrder ASC, wp_kivab_products.title", $cat );
								$return = '';
							
							$myrows = $wpdb->get_results( $sql );
							
							//run query here to check for slider content
							//$sql2 = $wpdb->prepare("SELECT * FROM wp_kivab_category_sliders WHERE catID = %s", $cat);
							//$slide = $wpdb->get_results($sql2);
								
							//run query for video content
							//$sql3 = $wpdb->prepare("SELECT * FROM wp_kivab_category_videos WHERE catID = %s ORDER BY vidOrder", $cat);
							//$vids  = $wpdb->get_results($sql3);
								
							//if there is a slider set the id
							/*if($slide){
								foreach ($slide as $slide){
									$sliderID = $slide->sliderID;
								}
							
								echo '
								<div id="layerslider" style="width:890px;height:420px;margin-bottom:180px;">';
								//get slides for slider
								$sql3 = $wpdb->prepare("SELECT * FROM wp_kivab_category_slides WHERE sliderID = %s ORDER BY slideOrder ASC",$sliderID);
									
								$myslides = $wpdb->get_results($sql3);
									
								foreach ($myslides as $myslides){
									echo '<div class="ls-slide" data-ls="">';
									$slideContent = stripslashes($myslides->slideContent);
									echo stripslashes($slideContent );
									echo '</div>';
								}
									
							
									
								echo '</div><div class="clear"></div>
								';
								$slider = true;
							}
							else{
								$slider = false;
							}
								
							
							//Loop out slider content
							
							echo '<div class="kivab-category-description">' .$myrows[0]->categoryDesc;
							//echo $fixed_url;
							//echo $myURL;
							echo '</div>';
							if($slider == true){
									
								//video container and video
							
							
									
								$j = 0;
								foreach($vids as $vids){
									if($j == 0){
										$return .= '<div class="catVidCont">';
										$return .= '<h2>'.$vids->vidTitle .'</h2>';
										$vidContent = stripslashes($vids->vidContent);
										$return .= stripslashes($vidContent);
										$return .= '</div>';
										$j++;
									}
									else{
											
										if($j==1){
											$return .= "<div class='moreVids'>More videos featuring these products:<br />";
										}
										$vidUrl = stripslashes($vids->vidURL );
										$vidThumb = str_replace('http://www.youtube.com/watch?v=', '', stripslashes($vids->vidURL));
										$return .= '<div class="vDiv">';
										$return .= '<a href="' . stripslashes($vidUrl) . '" title="'.$vids->vidTitle. '" target="_blank">';
										$return .= '<img src="http://img.youtube.com/vi/'.$vidThumb .'/0.jpg" alt="'. trim($vids->vidTitle).'"  class="cvThumb" /><br />';
										$return .= trim($vids->vidTitle). '</a></div>';
										$j++;
											
									}
										
								}
								if($j >= 1){
									$return .= "</div>";
								}
							
							
								$return .= '</div>';
								echo '<div class="slider-products">';
							}*/
							$return = '<div class="category-filters">';
							if($myrows[0]->generation == 2){
								$return .= '<ul><li><a href="/legacy-products/" title="View All Legcay Products">View All Legacy Products</a></li></ul>';
							}
							$return .= '</div><div class="kivab-categories">';
							foreach ($myrows as $myrows) {

									if($myrows->generation == 3){
										$pathOfPage = get_bloginfo('wpurl') . '/products/' . strtolower(str_replace(" ", "-", $myrows->categoryName)) . '/' . strtolower(str_replace("/","-",str_replace(" ", "-", $myrows->title))).'/';
									}
									else{
										$pathOfPage = get_bloginfo('wpurl') . '/legacy-products/' . strtolower(str_replace(" ", "-", $myrows->categoryName)) . '/' . strtolower(str_replace("/","-",str_replace(" ", "-", $myrows->title))).'/';
									}
									$pathOfPage = str_replace("&", "", $pathOfPage);
									$pathOfPage = str_replace("---", "-", $pathOfPage);
									$pathOfPage = str_replace("--", "-", $pathOfPage);
				
									$ret = file_get_contents( $wp_kivab_dir . '/categoryprod.template.xhtml' );
									//$ret = str_replace( '{url}', get_bloginfo('wpurl') . $fixed_url . '?id=' . $myrows->id . '&amp;product-name='. urlencode($myrows->title) . '&amp;category-name=' . urlencode($myrows->categoryName), $ret );
									$ret = str_replace( '{catID}', $mycategories->catID, $ret);
									$ret = str_replace( '{produrl}', $pathOfPage, $ret );
									$ret = str_replace( '{prodimg}', ( $myrows->imgurl ? $myrows->imgurl : $options['defimg'] ), $ret );
									$ret = str_replace( '{prodname}', $myrows->title, $ret );
									$return .= "\n\n". $ret . "\n";

							} // End foreach
							$return .= '</div>';
							/*
							if($slider == true){
								$return .= '</div>';
							
									
								//run query for meta benfits content
								$sql4 = $wpdb->prepare("SELECT * FROM wp_kivab_category_meta WHERE catID = %s AND metaColumn = 'benefits' ORDER BY metaOrder ASC", $cat);
								$benifits  = $wpdb->get_results($sql4);
									
								//meta container
								$return .= '<div class="metaContainer">';
									
								//Benifits container
								$return .= '<div class="metaColumn">
								<strong>Benefits</strong><br />
								<ul>';
								foreach ($benifits as $benifits){
									$metaContent = stripslashes($benifits->content);
									$return .= '<li>'.stripslashes($metaContent) .'</li>';
								}
									
								$return .= '</ul></div>';
									
								//run query for meta technology content
								$sql5 = $wpdb->prepare("SELECT * FROM wp_kivab_category_meta WHERE catID = %s AND metaColumn = 'technology' ORDER BY metaOrder ASC", $cat);
								$tech  = $wpdb->get_results($sql5);
									
								//technology container
									
								$return .= '<div  class="metaColumn">
								<strong>Technology</strong><br />
								<ul>';
							
								foreach ($tech as $tech){
									$metaContent = stripslashes($tech->content);
									$return .= '<li>'.stripslashes($metaContent) .'</li>';
								}
									
								$return .= '</ul></div>';
									
								//run query for meta related products content
								$sql6 = $wpdb->prepare("SELECT * FROM wp_kivab_category_meta WHERE catID = %s AND metaColumn = 'related products' ORDER BY metaOrder ASC", $cat);
								$related  = $wpdb->get_results($sql6);
									
								//related products container
								$return .= '<div  class="metaColumn">
								<strong>Related Products</strong><br />
								<ul>';
									
									foreach ($related as $related){
										$metaContent = stripslashes($related->content);
										$return .= '<li>'.stripslashes($metaContent) .'</li>';
									}
										
									$return .= '</ul></div>';
										
									//run query for meta system diagram content
									//$sql7 = $wpdb->prepare("SELECT * FROM wp_kivab_category_meta WHERE catID = %s AND metaColumn = 'system diagram' ORDER BY metaOrder ASC", $cat);
									//$diagram  = $wpdb->get_results($sql7);
										
									//system diagram image
									//$return .= '<div  class="metaColumn">
									//<strong>System Diagram</strong><br />';
										
									//foreach ($diagram as $diagram){
									//	$metaContent = stripslashes($diagram->content);
									//	$return .= '<li>'.stripslashes($metaContent) .'</li>';
									//}
										
										
									//$return .= '</div>';
										
										
									$return .= '</div>';
									$return .= '<div style="width:100%; float:left;clear:both;height:10px;">&nbsp;</div>';
								}*/
								return $return;
						
						}
						else{
							
							// global category level display (0)
							global $table_prefix, $wpdb;
							$return = '';
							$ret = '';
							
							//echo $myURL;
							$sql2 = $wpdb->prepare( "SELECT * FROM wp_kivab_categories WHERE sortOrder != 99 AND generation != 2 ORDER BY sortOrder ASC");
								
							$mycategories = $wpdb->get_results($sql2);
							
							//loop all the categories
							foreach ($mycategories as $mycategories) {
								//get all the products for each category 
								$table_name = $wpdb->prefix . "kivab_products";
								$sql = $wpdb->prepare( "SELECT * FROM $table_name INNER JOIN wp_kivab_categories ON wp_kivab_categories.catID = wp_kivab_products.category  
										WHERE wp_kivab_products.category=%s AND wp_kivab_products.isActive != 0 ORDER BY wp_kivab_products.prodOrder ASC, wp_kivab_products.title", $mycategories->catID );
								$myrows = $wpdb->get_results( $sql );
								foreach ($myrows as $myrows) {
									$pathOfPage = get_bloginfo('wpurl') . '/products/' . strtolower(str_replace(" ", "-", $myrows->categoryName)) . '/' . strtolower(str_replace("/","-",str_replace(" ", "-", $myrows->title))).'/';
									$pathOfPage = str_replace("&", "", $pathOfPage);
									$pathOfPage = str_replace("---", "-", $pathOfPage);
									$pathOfPage = str_replace("--", "-", $pathOfPage);
				
									$ret = file_get_contents( $wp_kivab_dir . '/categoryprod.template.xhtml' );
									//$ret = str_replace( '{url}', get_bloginfo('wpurl') . $fixed_url . '?id=' . $myrows->id . '&amp;product-name='. urlencode($myrows->title) . '&amp;category-name=' . urlencode($myrows->categoryName), $ret );
									$ret = str_replace( '{catID}', $mycategories->catID, $ret);
									$ret = str_replace( '{produrl}', $pathOfPage, $ret );
									$ret = str_replace( '{prodimg}', ( $myrows->imgurl ? $myrows->imgurl : $options['defimg'] ), $ret );
									$ret = str_replace( '{prodname}', $myrows->title, $ret );
									$return .= "\n\n". $ret . "\n";
								}
								
							} // End foreach
							$finalpage = file_get_contents( $wp_kivab_dir . '/category.template.xhtml' );
							$finalpage = str_replace('{prods}', $return, $finalpage);
							return $finalpage;
						}//end else
					}//end category level display
					
					else {
//////////////////////////product level display

						global $table_prefix, $wpdb;
						$fixed_url = substr($myURL, 0, strpos($myURL, '?'));
						$imgWin = $_GET['imgwin'];
						if($imgWin != true){
							$table_name = $wpdb->prefix . "kivab_products";
			
							if($cat<>"")
								
								$sql = $wpdb->prepare( "SELECT * FROM $table_name INNER JOIN wp_kivab_categories ON wp_kivab_categories.catID = wp_kivab_products.category  WHERE wp_kivab_products.id=%s ORDER BY title", $safeProd );
							else
						
							$sql = $wpdb->prepare( "SELECT * FROM $table_name" );
		
							$return = '';
							$myrows = $wpdb->get_results( $sql );
							
							$pathOfPage = get_bloginfo('wpurl') . '/products/' . strtolower(str_replace(" ", "-", $myrows[0]->categoryName)) . '/';
							$pathOfPage = str_replace("&", "", $pathOfPage);
							$pathOfPage = str_replace("---", "-", $pathOfPage);
							$pathOfPage = str_replace("--", "-", $pathOfPage);
							
							if($myrows[0]->generation == 3){
								echo '<div class="breadcrumb"><a href="/" title="home">Home</a> &#47; <a href="/products/" title="Products">Products</a> &#47; <a href="'.$pathOfPage.'" title="'.$myrows[0]->categoryName . '" >' . $myrows[0]->categoryName . '</a>  &#47; ' . str_replace("Bluetooth", "<em>Bluetooth</em>&reg;", $myrows[0]->title) . '</div>' ;
							}
							else{
								echo '<div class="breadcrumb"><a href="/" title="home">Home</a> &#47; <a href="/legacy-products/" title="Legacy Products">Legacy-Products</a> &#47; <a href="'.$pathOfPage.'" title="'.$myrows[0]->categoryName . '" >' . $myrows[0]->categoryName . '</a>  &#47; ' . str_replace("Bluetooth", "<em>Bluetooth</em>&reg;", $myrows[0]->title) . '</div>' ;
							}
							

							foreach ($myrows as $myrows) {
								global $wpdb;
								
								$myvids = $wpdb->get_results( 'SELECT * FROM wp_kivab_videos WHERE product_id = ' . intval($safeProd) . ' ORDER BY vOrder ASC');
								$vid_res = $myvids[0]->vTitle;
								$mydocs = $wpdb->get_results( 'SELECT * FROM wp_kivab_documents WHERE productID = ' . intval($safeProd) . ' ORDER BY docOrder ASC');
								$myimgs = $wpdb->get_results( 'SELECT * FROM wp_kivab_images WHERE productID = ' . intval($safeProd) . ' ORDER BY imgOrder ASC');
								$my360 = $wpdb->get_results( "SELECT * FROM wp_kivab_360_images WHERE productID=" . intval($safeProd));
								$myspecs = $wpdb->get_results( 'SELECT * FROM wp_kivab_product_specs WHERE product_id = ' . intval($safeProd) . ' ORDER BY specOrder ASC');
								//echo $chk;
								$ret = file_get_contents( $wp_kivab_dir . '/product.template.xhtml' );
								//$ret = str_replace( '{url}', $myrows->url . '?id=' . $myrows->id, $ret );
								//Kiva replacement code. Add custom url with product id.
								$ret = str_replace( '{backitup}', get_bloginfo('wpurl').$myUrl, $ret );
								$ret = str_replace( '{url}', get_bloginfo('wpurl') . $myUrl . '?id=' . $myrows->id, $ret );
								if(!$my360){

									$ret = str_replace( '{imgurl}',$myrows->imgurl , $ret );
									$ret = str_replace( '{360}', "", $ret );
								}
								else{
									//fun with strings!! 
									foreach($my360 as $my360){
										$counter = 1;
										$theImages = '';
										$imageString = '/images/360/'.$safeProd.'/' . $my360->fileName;
										for($x = 01; $x <= $my360->totalImages; $x++){
											if($x < 10){
												$x = str_pad($x, 2, "0", STR_PAD_LEFT);
											}
											$theImages .= '/images/360/' . $my360->productID . '/' .$my360->fileName . $x .'.jpg';
											if($x != $my360->totalImages){
												$theImages .= ',';
											}
										}
										
										$spinString = '<div id="360Image" class="threesixtyimage"><img src="'.$imageString .'01'.'.jpg" 
																width="937"
																height="600"
																id="360Image"
																class="reel"
																data-wheelable="true"
																data-images="'.$theImages .'"
																data-speed="0"
																data-frames="'.$my360->totalImages.'"
														><div id="spinIcon"></div></div>';
										
										
										$ret = str_replace( '{360}', $spinString, $ret );
										$ret = str_replace( '{imgurl}', "", $ret );
									}
								}
								$ret = str_replace( '{title}', $myrows->title, $ret );
								if($myrows->url != ""){
									$myquickdesc = stripslashes($myrows->url);
									$ret = str_replace ( '{quickdesc}', stripslashes($myquickdesc), $ret );
								}
								else{
									$ret = str_replace ( '', stripslashes($myquickdesc), $ret );
								}
								if($myrows->imgurlmode != ""){
									$cartLink = '<div id="buynow"><a href="'. $myrows->imgurlmode . '" title="Purchase this item online">Buy Now</a></div>';
									$ret = str_replace( '{cartlink}', $cartLink , $ret );
								}
								else{
									$ret = str_replace( '{cartlink}', "" , $ret );
								}
								$thefinaldesc = stripslashes($myrows->description);
								$ret = str_replace( '{description}', stripslashes($thefinaldesc) , $ret );
								if($myspecs[0]->spectitle != ""){
									$ret = str_replace( '{subtable}', kivab::createsubtable($myrows->id), $ret );
								}
								else{
									$ret = str_replace( '{subtable}', "No product specifcations available for this product", $ret );
								}
								$ret = str_replace( '{images}', kivab::createimagetable($myrows->id), $ret );
								if($vid_res != ""){
									$ret = str_replace( '{videos}', kivab::createvideotable($myrows->id), $ret );
								}
								else {
									$ret = str_replace( '{videos}', 'No video available', $ret );
								}
								if($mydocs[0]->documentTitle != ""){
									$ret = str_replace( 'No documents available', kivab::createdocumenttable($myrows->id), $ret );
								}
								$return .= "\n\n". $ret . "\n";
							} // End foreach
							return $return;
						}//end if for image window
						else{
							kivab::createimagepage($safeProd);
						}
					}
					
			} // End createtable()
			

			public function createsubtable($id) {
				global $table_prefix, $wpdb;
				global $wp_kivab_url;
				global $wp_kivab_dir;
				global $wpmu_kivab_url;
				global $wpmu_kivab_dir;

				$table_name = $wpdb->prefix . "kivab_product_specs";

				$return = '';
				$myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE product_id=" . intval($id) . " ORDER BY specOrder ASC" );
				
				foreach ($myrows as $myrows) {
					$ret = file_get_contents( $wp_kivab_dir . '/subtable.template.xhtml' );
					$ret = str_replace( '{spectitle}', "<li>". $myrows->spectitle, $ret );
					if($myrows->specvalue != ""){
					$ret = str_replace( '{specvalue}', ': ' . $myrows->specvalue."</li>", $ret );
					}
					else{
						$ret = str_replace( '{specvalue}', "", $ret );
					}
					$return .= $ret;
				} // End foreach
				return $return;
			} // End createsubtable()
			
			//function for images - zoomy
			public function createimagepage($id) {
				global $table_prefix, $wpdb;
				global $wp_kivab_url;
				global $wp_kivab_dir;
				global $wpmu_kivab_url;
				global $wpmu_kivab_dir;
				
				$table_name = $wpdb->prefix . "kivab_images";
				
				$return = '';
				$myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE productID=" . intval($id) ." ORDER BY imgOrder ASC" );
				$my360 = $wpdb->get_results( "SELECT * FROM wp_kivab_360_images WHERE productID=" . intval($id));
				$myMain = $wpdb->get_results( "SELECT * FROM wp_kivab_products WHERE id=" . intval($id));
				
				
				
				
				$finalPage = file_get_contents( $wp_kivab_dir . '/imagepage.template.xhtml' );
				//if there is a 360 image, use that, otherwise put the static image there.
				if($my360){
					$theimgpath = '/images/360/'.$id .'/';
					foreach ($my360 as $my360){
						$ret = file_get_contents( $wp_kivab_dir . '/thumb.template.xhtml' );
						
						$ret = str_replace( '{imgpath}', $theimgpath . $my360->fileName . '01.jpg', $ret );
						$ret = str_replace( '{imgalt}', "360", $ret );
						
						/// code for 360
						$counter = 1;
						$theImages = '';
						$imageString = '/images/360/'.$safeProd.'/' . $my360->fileName;
						for($x = 01; $x <= $my360->totalImages; $x++){
							if($x < 10){
								$x = str_pad($x, 2, "0", STR_PAD_LEFT);
							}
							$theImages .= '/images/360/' . $my360->productID . '/' .$my360->fileName . $x .'.jpg';
							if($x != $my360->totalImages){
								$theImages .= ',';
							}
						}
						
						$spinString = '<img src="'.$imageString .'01'.'.jpg"
						width="937"
						height="600"
						id="my_image"
						class="reel"
						data-wheelable="true"
						data-images="'.$theImages .'"
						data-speed="0"
						data-frames="'.$my360->totalImages.'"
						>';
						

						$return .= "\n\n". $ret . "\n";
						$finalPage = str_replace( '{360}', $spinString, $finalPage);
						
					}
				}
				
					foreach( $myMain as $myMain ) {
						$ret = file_get_contents( $wp_kivab_dir . '/thumb.template.xhtml' );
						$ret = str_replace( '{imgpath}', $myMain->imgurl, $ret );
						$ret = str_replace( '{imgalt}', $myMain->title, $ret );
						$return .= "\n\n". $ret . "\n";
						$finalPage = str_replace('{bigImage}', $myMain->imgurl, $finalPage);
					}
				
					foreach ($myrows as $myrows) {
						$ret = file_get_contents( $wp_kivab_dir . '/thumb.template.xhtml' );
						$ret = str_replace( '{imgpath}', $myrows->imgPath, $ret );
						$ret = str_replace( '{imgalt}', $myrows->imgAlt, $ret );
						$return .= "\n\n". $ret . "\n";
					} // End foreach
				$finalPage = str_replace('{thumbs}', $return, $finalPage);
				
				print $finalPage;
				
				
			}
			
			public function createimagetable($id) {
				global $table_prefix, $wpdb;
				global $wp_kivab_url;
				global $wp_kivab_dir;
				global $wpmu_kivab_url;
				global $wpmu_kivab_dir;
			
				$table_name = $wpdb->prefix . "kivab_images";
				$count = 0;
					$return = '';
					$myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE productID=" . intval($id) ." ORDER BY imgOrder ASC" );
					$myMain = $wpdb->get_results( "SELECT * FROM wp_kivab_products WHERE id=" . intval($id));
					$my360 = $wpdb->get_results( "SELECT * FROM wp_kivab_360_images WHERE productID=" . intval($id));
					
					if($my360){
						$theimgpath = '/images/360/'.$id .'/';
						foreach ($my360 as $my360){
							$ret = file_get_contents( $wp_kivab_dir . '/image.template.xhtml' );
							$ret = str_replace( '{imgwin}', 'http://' .$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] .'?imgwin=true', $ret );
							$ret = str_replace( '{imgpath}', $theimgpath . $my360->fileName . '01.jpg', $ret );
							$ret = str_replace( '{imgalt}', "360", $ret );
			
					
					
							$return .= "\n\n". $ret . "\n";
							//$finalPage = str_replace( '{360}', $spinString, $finalPage);
							$count++;
						}
					}
					foreach( $myMain as $myMain ) {
						$ret = file_get_contents( $wp_kivab_dir . '/image.template.xhtml' );
						$ret = str_replace( '{imgwin}', 'http://' .$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] .'?imgwin=true', $ret );
						$ret = str_replace( '{imgpath}', $myMain->imgurl, $ret );
						$ret = str_replace( '{imgalt}', $myMain->title, $ret );
						$return .= "\n\n". $ret . "\n";
						$finalPage = str_replace('{bigImage}', $myMain->imgurl, $finalPage);
						$count++;
					}
				
					foreach ($myrows as $myrows) {
						if($count <= 2){
							$ret = file_get_contents( $wp_kivab_dir . '/image.template.xhtml' );
							$ret = str_replace( '{imgwin}', 'http://' .$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] .'?imgwin=true', $ret );
							$ret = str_replace( '{imgpath}', $myrows->imgPath, $ret );
							$ret = str_replace( '{imgalt}', $myrows->imgAlt, $ret );
							$return .= "\n\n". $ret . "\n";
							$count++;
						}
					} // End foreach
				return $return;
			} // End createimagetable()
			
			public function createvideotable($id) {
				global $table_prefix, $wpdb;
				global $wp_kivab_url;
				global $wp_kivab_dir;
				global $wpmu_kivab_url;
				global $wpmu_kivab_dir;
					
				$table_name = $wpdb->prefix . "kivab_videos";
					
				$return = '';
				$myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE product_id=" . intval($id) ." ORDER BY vOrder ASC" );
				$k = 0;
				foreach ($myrows as $myrows) {
					
					if($k == 0){
						$ret1 = file_get_contents( $wp_kivab_dir . '/first-video-template.xhtml' );
						$ret1 = str_replace( '{mainTitle1}', $myrows->vTitle, $ret1 );
						$ret1 = str_replace( '{videoURL1}', $myrows->vURL, $ret1 );
						$ret1 = str_replace( '{title1}', $myrows->vTitle, $ret1 );
						$ret1 = str_replace( '{desc1}', $myrows->vDesc, $ret1 );
						$return .= "\n\n". $ret1 . "\n";
						$k ++;
						$ret = file_get_contents( $wp_kivab_dir . '/video-template.xhtml' );
						$ret = str_replace( '{mainTitle}', $myrows->vTitle, $ret );
						$ret = str_replace( '{videoURL}', $myrows->vURL, $ret );
						$ret = str_replace( '{title}', $myrows->vTitle . ' <span style="color:#fb6417">*Current</span>', $ret );
						$ret = str_replace( '{vidID}', $myrows->vID, $ret );
						$ret = str_replace( '{desc}', $myrows->vDesc, $ret );
						$return .= "\n\n". $ret . "\n";
					}
					else{
					$ret = file_get_contents( $wp_kivab_dir . '/video-template.xhtml' );
					$ret = str_replace( '{mainTitle}', $myrows->vTitle, $ret );
					$ret = str_replace( '{videoURL}', $myrows->vURL, $ret );
					$ret = str_replace( '{title}', $myrows->vTitle, $ret );
					$ret = str_replace( '{vidID}', $myrows->vID, $ret );
					$ret = str_replace( '{desc}', $myrows->vDesc, $ret );
					$return .= "\n\n". $ret . "\n";
					}

				} // End foreach
				return $return;
			} // End createvideotable()
			
			public function createdocumenttable($id) {
				global $table_prefix, $wpdb;
				global $wp_kivab_url;
				global $wp_kivab_dir;
				global $wpmu_kivab_url;
				global $wpmu_kivab_dir;
					
				$table_name = $wpdb->prefix . "kivab_documents";
					
				$return = '';
				$myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE productID=" . intval($id) ." ORDER BY docOrder ASC" );
				foreach ($myrows as $myrows) {
					$ret = file_get_contents( $wp_kivab_dir . '/document.template.xhtml' );
					$ret = str_replace( '{documentURL}', $myrows->documentURL, $ret );
					$ret = str_replace( '{documentTitle}', $myrows->documentTitle, $ret );
					$return .= "\n\n". $ret . "\n";
				} // End foreach
				return $return;
			} // End createimagetable()
			
			// //////////////////////////////////////////////////////////////////////////////////////////////////////////// //
			//                                                                                                              //
			//   OPTION PAGE                                                                                                //
			//                                                                                                              //
			// //////////////////////////////////////////////////////////////////////////////////////////////////////////// //
			
			public static function OptionPage() {
				// get options
				$options = $newoptions = get_option('kivab_options');
				
				$dir_name = '/wp-content/plugins/kivab';
				$url = get_bloginfo('wpurl');
				$myURL = $url.$dir_name.'/';
//				printf( __('My URL: %s.', 'kivab'), $myURL);

				// Check for permissions
				if (!current_user_can('manage_options'))  {
					wp_die( __('You do not have sufficient permissions to access this page.') );
				}

				global $table_prefix, $wpdb;
				$table_name = $wpdb->prefix . "kivab_products";
				$subtable_name = $wpdb->prefix . "kivab_product_specs";

				// if submitted, process results
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="options") {
					echo "*** Updating options!!! ***";
					$newoptions['width'] = strip_tags(stripslashes($_POST["width"]));
					$newoptions['wptouchwidth'] = strip_tags(stripslashes($_POST["wptouchwidth"]));
					$newoptions['defimg'] = strip_tags(stripslashes($_POST["defimg"]));

					if ($_POST["visibleerrors"]=="on") {
						$newoptions['visibleerrors'] = "true";
					} else {
						$newoptions['visibleerrors'] = "";
					}

					// //////////////////////////////////////////////////////////// //
					// check if installed (hook is not called if used as mu-plugin) //
					// //////////////////////////////////////////////////////////// //
					//$wtemp = get_option('kivab_width');
					//if( empty($wtemp) ){
					//	echo "WHAT NOT INST?!? ****";
					//	kivab::kivab_activate();
					//}
				}

				// if product selected, save selection persistantly as an option
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productselect") {
					$newoptions['selectproduct'] = strip_tags(stripslashes($_POST["selectproduct"]));
					// Update selected product option
					update_option('kivab_selectproduct', $_POST['kivab_selectproduct']);
				}

				// any changes? save!
				if ( $options != $newoptions ) {
					$options = $newoptions;
					update_option('kivab_options', $options);
				}

				// If needed, add a product
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productadd") {
					$wpdb->query(
						$wpdb->prepare( "INSERT INTO $table_name ( category, title, description, url, imgurl, imgurlmode, isActive, prodOrder ) VALUES ( %s, %s, %s, %s, %s, %s, %s, %s )",
										array(
												'12',
												'New Product',
												'Please type a description',
												' ',
												'http://s.wordpress.org/about/images/wordpress-logo-notext-bg.png',
												'',
												'1',
												'10'
										)
						)
					);
				}

				// If needed, add a specification row
//				if ($_POST["addspec"]=="on") {
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productaddspec") {
					$wpdb->query(
						$wpdb->prepare( "INSERT INTO $subtable_name ( product_id, spectitle, specvalue, specOrder ) VALUES ( %d, %s, %s, %s )",
										array($options['selectproduct'], 'Specification', 'Value', '10') )
					);
				}
				
				
				// If needed, add a img row
				//				if ($_POST["addspec"]=="on") {
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productaddimg") {
					$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_images ( productid, imgpath, imgalt, imgOrder ) VALUES ( %d, %s, %s, %s )",
									array($options['selectproduct'], 'image path', 'image alt text', '10') )
					);
				}
				
				
				// ////////////////////////////////////////////////////////// //
				// category handling - db interaction                           //
				// ////////////////////////////////////////////////////////// //
				
				//add category if called
				
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productaddcategory") {
						$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_categories ( categoryName, categoryDesc, categoryImg, sortOrder ) VALUES ( %s, %s, %s, %s )",
										array('New Category', 'category description', 'image url', '10'))
										);
				}
				
				// if category form was sent, process those...
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="updatecategory") {
					
					$currentCat = $_POST['catID'];
					// Update the category info
					$wpdb->query(
					$sql = $wpdb->prepare( "UPDATE wp_kivab_categories SET categoryName =%s, categoryDesc =%s, categoryImg =%s, sortOrder = %s WHERE catID =%s",
							array(
									mysql_real_escape_string($_POST['categoryName']),
									mysql_real_escape_string($_POST['categoryDesc']),
									mysql_real_escape_string($_POST['categoryImg']),
									mysql_real_escape_string($_POST['sortOrder']),
									mysql_real_escape_string($_POST['catID'])
							))
					);
					
					$isitset = $wpdb->get_results("SELECT * FROM wp_kivab_category_sliders WHERE catID = ". $_POST['catID']);
					//echo $_POST['categorySlider'];
					
					if($_POST['categorySlider']){
						
						if(!$isitset){
							$wpdb->query(
									$sql = $wpdb->prepare( "INSERT INTO wp_kivab_category_sliders (catID) VALUES( %s)",
											array(
													mysql_real_escape_string($_POST['catID'])
											))
							);
						}
					}//end if for adding slider
					
					if(!$_POST['categorySlider']){
						$wpdb->query("DELETE FROM wp_kivab_category_sliders WHERE catID = " .$_POST['catID']);
						echo "else has fired";
					}
					
					//update slides//
					
					foreach($_POST["slides"] as $slides) {
						
						if($slides['slideContent'] != "") {
							$sql = $wpdb->prepare( "UPDATE wp_kivab_category_slides SET slideContent=%s, slideOrder=%s WHERE slideID = %s",
									array(mysql_real_escape_string($slides['slideContent']),$slides["slideOrder"], $slides["slideID"]  ) );
						} 
						else {
							echo "One slide being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM wp_kivab_category_slides WHERE slideID= ". $slides['slideID']);
						}
						$wpdb->query( $sql );
					}
					
					foreach($_POST["meta"] as $meta) {
					
						if($meta['content'] != "") {
							$sql = $wpdb->prepare( "UPDATE wp_kivab_category_meta SET content=%s, metaOrder=%s, metaColumn = %s WHERE metaID = %s",
									array(mysql_real_escape_string($meta['content']),$meta["metaOrder"],$meta["metaColumn"], $meta["metaID"]   ) );
						}
						else {
							echo "One meta entry being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM wp_kivab_category_meta WHERE metaID= ". $meta['metaID']);
						}
						$wpdb->query( $sql );
					}
					
					
					foreach($_POST["videos"] as $videos) {
							
						if($videos['vidContent'] != "") {
							$sql = $wpdb->prepare( "UPDATE wp_kivab_category_videos SET vidContent=%s, vidOrder=%s, vidURL = %s, vidTitle = %s WHERE catVidID = %s",
									array(mysql_real_escape_string($videos['vidContent']),$videos["vidOrder"], mysql_real_escape_string($videos["vidURL"]), mysql_real_escape_string($videos['vidTitle']), $videos["catVidID"]   ) );
						}
						else {
							echo "One video entry being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM wp_kivab_category_videos WHERE catVidID= ". $videos['catVidID']);
						}
						$wpdb->query( $sql );
					}
					
				}
				
				// category slides input
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="insertSlide") {
					$currentCat = $_POST['catID'];
					$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_category_slides (sliderID, slideOrder, slideContent  ) VALUES ( %s, %s, %s )",
									array($_POST['sliderID'], 100, '<img src="" class="ls-bg" alt="Slide background"/><a href="" class="ls-link"></a>' ))
					);
					
				}
				
				// category insert meta
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="insertMeta") {
					$currentCat = $_POST['catID'];
					$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_category_meta (catID, metaOrder, content, metaColumn  ) VALUES ( %s, %s, %s,%s )",
									array($_POST['catID'], 100, ' ', $_POST['metaColumn']))
					);
						
				}
				
				// category insert video
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="insertVideo") {
					$currentCat = $_POST['catID'];
					$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_category_videos (catID, vidOrder, vidContent,  vidTitle, vidURL  ) VALUES ( %s, %s, %s,%s,%s )",
									array($_POST['catID'], 100, 'Youtube Embed Code Here', "New Video", "Link to video here"))
					);
				
				}
				
				// ////////////////////////////////////////////////////////// //
				//product video handling - db interaction                           //
				// ////////////////////////////////////////////////////////// //
				
				//add video if called
				
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productaddvideo") {
					$currentCat = $_POST['catID'];
					$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_videos ( vTitle, vURL, vOrder, vDesc, product_id ) VALUES ( %s, %s, %s, %s,%d )",
									array('New Video', 'url', '10', ' ', $options['selectproduct'] ))
					);
				}
				

				
				// ////////////////////////////////////////////////////////// //
				// documents creation and handling                            //
				// ////////////////////////////////////////////////////////// //
				
				
				//add document if called
				
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productadddoc") {
					$wpdb->query(
							$wpdb->prepare( "INSERT INTO wp_kivab_documents ( productID, documentTitle, documentURL, docOrder ) VALUES ( %d, %s, %s, %s )",
									array($options['selectproduct'], 'Document Title', 'Document URL','10'))
					);
				}
				
				// if document form was sent, process those...
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productupdatedoc") {
				
					// Update the document info
					$wpdb->query(
							$sql = $wpdb->prepare( "UPDATE wp_kivab_documents SET documentTitle =%s, documentURL =%s, docOrder = %s WHERE documentID =%s",
									array(
											mysql_real_escape_string($_POST['documentTitle']),
											mysql_real_escape_string($_POST['documentURL']),
											mysql_real_escape_string($_POST['docOrder']),
											mysql_real_escape_string($_POST['documentID'])
									))
					);
				}
				
				
				// ////////////////////////////////////////////////////////// //
				// option handling - db interaction                           //
				// ////////////////////////////////////////////////////////// //

				// if options form was sent, process those...
				if ( isset($_POST["kivab_submit"]) && $_POST["kivab_submit"]=="productupdate") {
					//if( isset($_POST['action']) && $_POST['action'] == "updateoptions" ){

					// Update the product info
					$sql = $wpdb->prepare( "UPDATE $table_name SET category=%s, title=%s, description=%s, url=%s, imgurl=%s, prodOrder=%s, imgurlmode=%s WHERE id=%d",
											array(
												mysql_real_escape_string($_POST[prodcat]),
												mysql_real_escape_string($_POST[prodtitle]),
												mysql_real_escape_string($_POST[proddesc]),
												mysql_real_escape_string($_POST[produrl]),
												mysql_real_escape_string($_POST[prodimgurl]),
												mysql_real_escape_string($_POST[prodOrder]),
												mysql_real_escape_string($_POST[prodimgurlmode]),
												$options['selectproduct']
											)
					);
					$wpdb->query( $sql );
					//echo "SQL: $sql<br />";

					// Update the product spec
					foreach($_POST["spec"] as $spec) {
						if($spec[title]!="") {
							$sql = $wpdb->prepare( "UPDATE $subtable_name SET spectitle=%s, specvalue=%s, specOrder = %s WHERE product_id=%d AND id=%d",
													array($spec[title], $spec[value], $spec[specOrder], $options['selectproduct'], $spec[id] ) );
						} else {
							echo "One specification line item being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM $subtable_name WHERE product_id=%d AND id=%d",
													array($options['selectproduct'], $spec[id] ) );
						}
						$wpdb->query( $sql );
					}
					
					// Update the product images
					foreach($_POST["images"] as $images) {
						if($images[imgPath]!="") {
							$sql = $wpdb->prepare( "UPDATE wp_kivab_images SET imgPath=%s, imgAlt=%s, imgOrder = %s WHERE productid=%d AND imgID=%d",
									array($images[imgPath], $images[imgAlt],$images[imgOrder], $options['selectproduct'], $images[imgID] ) );
						} else {
							echo "One image being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM wp_kivab_images WHERE productid=%d AND imgID=%d",
									array($options['selectproduct'], $images[imgID] ) );
						}
						$wpdb->query( $sql );
					}
					
					// Update the product documents
					foreach($_POST["documents"] as $documents) {
						if($documents[documentURL]!="") {
							$sql = $wpdb->prepare( "UPDATE wp_kivab_documents SET documentURL=%s, documentTitle=%s, docOrder=%s WHERE productid=%d AND documentID=%d",
									array($documents[documentURL], $documents[documentTitle], $documents[docOrder], $options['selectproduct'], $documents[documentID] ) );
						} else {
							echo "One image being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM wp_kivab_documents WHERE productid=%d AND documentID=%d",
									array($options['selectproduct'], $documents[documentID] ) );
						}
						$wpdb->query( $sql );
					}
					// Update the product videos
					foreach($_POST["videos"] as $videos) {
						//echo "at least this is working....";
						if($videos[vURL]!="") {
							$sql = $wpdb->prepare( "UPDATE wp_kivab_videos SET vURL=%s, vTitle=%s, vOrder=%s, vDesc=%s WHERE product_id=%d AND vID=%s",
									array($videos[vURL], $videos[vTitle], $videos[vOrder], $videos[vDesc], $options['selectproduct'], $videos[vID] ) );
						} else {
							echo "One video being deleted!<br />";
							$sql = $wpdb->prepare( "DELETE FROM wp_kivab_videos WHERE product_id=%d AND vID=%d",
									array($options['selectproduct'], $documents[vID] ) );
						}
						$wpdb->query( $sql );
					}

				}

				
				
				// ////////////////////////////////////////////////////////// //
				// Build Product Option Page!
				// ////////////////////////////////////////////////////////// //
//				$product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));

				echo '<div class="wrap" style="min-height:1600px;"><div class="wrap"><p><h2>'.__('Sonetics Products and Categories', 'kivab').'</h2>*This application is in beta. </p>';
//				echo '<p>';
//				echo 'Total number of items in database: <b>' . $product_count . '</b> ';
//				echo '( Currently selected item ID: ' . $options['selectproduct'] . ' )';
//				echo '</p>';
				echo '</div>';

				// ////////////////////////////////////////////////////////// //
				// Product creation
				// ////////////////////////////////////////////////////////// //

				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				echo '<div class="wrap">';
				echo '<input type="hidden" name="kivab_submit" value="productadd"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Create new Product &raquo;', 'kivab').'"></input></p>';
				echo "</div>";
				echo '</form>';

				// ////////////////////////////////////////////////////////// //
				// Product selection
				// ////////////////////////////////////////////////////////// //

				$product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				echo '<div class="wrap">';
				$myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " INNER JOIN wp_kivab_categories ON wp_kivab_categories.catID = wp_kivab_products.category ORDER BY wp_kivab_products.Category, wp_kivab_products.Title");
				echo '<select name="selectproduct" onchange=\'this.form.submit()\'>';
				foreach ($myrows as $myrows) {
					echo '<option value=' . $myrows->id . ''. ($myrows->id==$options['selectproduct']?' SELECTED':'') . '>' . $myrows->categoryName . ' | ' . $myrows->title . '';
				}
				echo '</select>';

				// SUBMIT
				echo '<input type="hidden" name="kivab_submit" value="productselect"></input>';	
				echo '<span class="submit"><input type="submit" value="'.__('Select Product &raquo;', 'kivab').'"></input></span>';
				echo "</div>";
				echo '</form>';


				// ////////////////////////////////////////////////////////// //
				// Products Form
				// ////////////////////////////////////////////////////////// //

				$mycurrentprod = $wpdb->get_results( "SELECT * FROM " . $table_name . " INNER JOIN wp_kivab_categories ON wp_kivab_categories.catID = wp_kivab_products.category WHERE wp_kivab_products.ID=".intval($options['selectproduct']) );
				$mycategories = $wpdb->get_results( "SELECT * FROM wp_kivab_categories ORDER BY sortOrder" );
				$selectedcat= $wpdb->get_results( "SELECT catID FROM wp_kivab_categories INNER JOIN wp_kivab_products ON wp_kivab_categories.catID = wp_kivab_products.category WHERE wp_kivab_products.ID=".intval($options['selectproduct']) );
				// settings
				echo 'To include this product on a page, paste the following shortcode into your post: [kivab x-'.$mycurrentprod[0]->id.']';
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';

				// Image thumbnail
//				echo '<div style="float:right; max-width:160px; max-height:160px; ">';
//				echo '<img src="'.$mycurrentprod[0]->imgurl.'" style="float:right; max-width:160px; max-height:160px; " />';
//				echo '</div>';

				echo '<div class="wrap">';

				echo '<table class="form-table">';

				// Category and Title
				echo '<tr valign="top"><th scope="row">'.__('Category and Title', 'kivab').'</th>';
				echo '<td>';
				$mycat = $selectedcat[0]->catID;
				echo '<select name="prodcat">';
					foreach ($mycategories as $mycategories) {	
						if ($mycategories ->catID == $mycat){
				 		echo '<option value="' . $mycategories ->catID. '" SELECTED>' . $mycategories->categoryName. '</option>';
						}
						else{
							echo '<option value="' . $mycategories ->catID. '">' . $mycategories->categoryName. '</option>';
						}
				 		}
				 
				echo '</select>';
				echo '<input type="text" name="prodtitle" value="'.$mycurrentprod[0]->title.'" size="35"></input>';
				echo '</td></tr>';
				
				// media center
				echo '<tr valign="top"><th scope="row">'.__('Media Center', 'kivab').'</th>';
				echo '<td>';
				echo '<a href="/wp-admin/upload.php?TBiframe=1" class="iframec" title="Add Media">Upload or find file</a>';
				echo '</td></tr>';
				
				// cart link 
				echo '<tr valign="top"><th scope="row">'.__('Cart Link (leave blank if not available for e-commerce)', 'kivab').'</th>';
				echo '<td>';
				echo '<input type="text" name="prodimgurlmode" value="'.$mycurrentprod[0]->imgurlmode.'" size="80"></input>';
				echo '</td></tr>';
				
				
				// Image URL
				echo '<tr valign="top"><th scope="row">'.__('URL to Product Image', 'kivab').'</th>';
				echo '<td>';
				echo '<input type="text" name="prodimgurl" value="'.$mycurrentprod[0]->imgurl.'" size="80"></input>';
				echo '</td></tr>';

				// Description
				echo '<tr valign="top"><th scope="row">'.__('Product Description', 'kivab').'</th>';
				echo '<td>';
				
				$theProdDesc = stripslashes( $mycurrentprod[0]->description );
				
				echo '<textarea name="proddesc" rows=8 cols=80>'. stripslashes($theProdDesc) .'</textarea>';
				echo '<img src="'.$mycurrentprod[0]->imgurl.'" style="float:right; max-width:160px; max-height:160px; " />';
				echo '</td></tr>';
				
				//product Sort ORder
				echo '<tr valign="top"><th scope="row">'.__('Product Order', 'kivab').'</th>';
				echo '<td><input type="text" name="prodOrder" value="'.$mycurrentprod[0]->prodOrder.'" size="80"></input>';
				echo '</td></tr>';
				
				
				// URL
				echo '<tr valign="top"><th scope="row">'.__('Quick Description', 'kivab').'</th>';
				echo '<td><input type="text" name="produrl" value="'.$mycurrentprod[0]->url.'" size="80"></input>';
				echo '</td></tr>';

				echo '</table>';

				//product specs
				$qry_string = 'SELECT * FROM '. $subtable_name .' WHERE product_id = '.intval($options['selectproduct']) .' ORDER BY specOrder ASC';
				$mycurrentspecs = $wpdb->get_results($qry_string) ;
				echo '<table class="form-table"  style="border:1px solid #ccc;">';
				
				foreach ($mycurrentspecs as $mycurrentspecs) {
					// Specifications
					echo "<tr valign=\"top\"><th scope=\"row\">".__('Specification', 'kivab')."</th>";
					echo "<td>";
					echo "<input type=\"text\" name=\"spec[".$mycurrentspecs->id."][title]\" value=\"".$mycurrentspecs->spectitle."\" size=\"35\" />";
					echo "<input type=\"text\" name=\"spec[".$mycurrentspecs->id."][value]\" value=\"".$mycurrentspecs->specvalue."\" size=\"25\" />";
					echo "<input type=\"text\" name=\"spec[".$mycurrentspecs->id."][specOrder]\" value=\"".$mycurrentspecs->specOrder."\" size=\"4\" />";
					echo "<input type=\"hidden\" name=\"spec[".$mycurrentspecs->id."][id]\" value=\"".$mycurrentspecs->id."\" />";
					echo "</td></tr>";
				}
				
				
				echo '</table>';
				
				//videos
				$myvids = 'wp_kivab_videos';
				$qry_string = 'SELECT * FROM ' . $myvids . ' WHERE product_id = '. intval($options['selectproduct']) . ' ORDER BY vOrder ASC';
				$mycurrentvids = $wpdb->get_results($qry_string ) ;
				echo '<table  style="border:1px solid #ccc;float:left;clear:left;margin:10px;width:70%;">';
				echo "<tr valign=\"top\"><th style='width:280px;' scope=\"row\">".__('Videos', 'kivab')."</th><th> </th></tr>";
				foreach ($mycurrentvids as $mycurrentvids) {
					// Specifications
					
					echo "<tr><td>Video Title: </td><td>";
					echo "<input type=\"text\" name=\"videos[".$mycurrentvids->vID."][vTitle]\" value=\"".$mycurrentvids->vTitle."\" size=\"35\" />";
					echo "</td></tr>";
					echo "<tr><td>Video URL: </td><td>";
					echo "<input type=\"text\" name=\"videos[".$mycurrentvids->vID."][vURL]\" value=\"".$mycurrentvids->vURL."\" size=\"35\" />";
					echo "</td></tr>";
					echo "<tr><td>Video Description: </td><td>";
					echo "<textarea name=\"videos[".$mycurrentvids->vID."][vDesc]\">".$mycurrentvids->vDesc."</textarea>";
					echo "</td></tr>";
					echo "<tr><td>Video Order (numbers only, 1 being first): </td><td>";
					echo "<input type=\"text\" name=\"videos[".$mycurrentvids->vID."][vOrder]\" value=\"".$mycurrentvids->vOrder."\" size=\"10\" />";
					echo "</td></tr>";
					echo "<tr><td colspan='2' style='background-color:#ccc;'> </td><td>";
					echo "<input type=\"hidden\" name=\"videos[".$mycurrentvids->vID."][vID]\" value=\"".$mycurrentvids->vID."\" />";
					//echo "<input type=\"hidden\" name=\"videos[".$mycurrentvids->vID."][vID]\" value=\"".$mycurrentvids->vID."\" />";
					
								}
				echo "</table>";
				// ////////////////////////////////////////////////////////// //
				// Multiple product images                                    //
				// ////////////////////////////////////////////////////////// //
				
				$mycurrentimgs = $wpdb->get_results( "SELECT * FROM  wp_kivab_images WHERE productid=".intval($options['selectproduct']). ' ORDER BY imgOrder ASC');
				echo '<table class="form-table"  style="border:1px solid #ccc;">';
				foreach ($mycurrentimgs as $mycurrentimgs) {
					// Specifications
					echo "<tr valign=\"top\"><th scope=\"row\">".__('Image', 'kivab')."</th>";
					echo "<td>";
					echo 'Image path: <input type="text" name="images['.$mycurrentimgs->imgID.'][imgPath]" value="'.$mycurrentimgs->imgPath. '" size="25" />';
					echo 'Image alt text: <input type="text" name="images['.$mycurrentimgs->imgID.'][imgAlt]" value="'.$mycurrentimgs->imgAlt.'" size="35" />';
					echo 'Image Order: <input type="text" name="images['.$mycurrentimgs->imgID.'][imgOrder]" value="'.$mycurrentimgs->imgOrder.'" size="4" />';
					echo '<input type="hidden" name="images['.$mycurrentimgs->imgID.'][imgID]" value="'.$mycurrentimgs->imgID.'" />';
					echo "</td></tr>";
				}
				
				//product documents
			$mycurrentdocs = $wpdb->get_results( "SELECT * FROM wp_kivab_documents WHERE productID=".intval($options['selectproduct']) . ' ORDER BY docOrder ASC');
				echo '<table class="form-table"   style="border:1px solid #ccc;">';
				foreach ($mycurrentdocs as $mycurrentdocs) {
					// Specifications
					echo "<tr valign=\"top\"><th scope=\"row\">".__('Documents', 'kivab')."</th>";
					echo "<td>";
					echo "Title: <input type=\"text\" name=\"documents[".$mycurrentdocs->documentID."][documentTitle]\" value=\"".$mycurrentdocs->documentTitle."\" size=\"15\" />";
					echo "URL: <input type=\"text\" name=\"documents[".$mycurrentdocs->documentID."][documentURL]\" value=\"".$mycurrentdocs->documentURL."\" size=\"35\" />";
					echo "Order: <input type=\"text\" name=\"documents[".$mycurrentdocs->documentID."][docOrder]\" value=\"".$mycurrentdocs->docOrder."\" size=\"4\" />";
					echo "<input type=\"hidden\" name=\"documents[".$mycurrentdocs->documentID."][documentID]\" value=\"".$mycurrentdocs->documentID."\" />";
					echo "</td></tr>";
				}
				
				echo '</table>';
				
				echo "</td></tr>";
				echo '</table>';
				echo "</td></tr>";
				echo '</table>';
				
				// SUBMIT
				echo '<input type="hidden" name="kivab_submit" value="productupdate"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Update Product &raquo;', 'kivab').'"></input></p>';
				echo "</div>";
				echo '</form>';


				// ////////////////////////////////////////////////////////// //
				// Product specs creation
				// ////////////////////////////////////////////////////////// //

				$product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				//echo '<div class="wrap">';
				echo '<input type="hidden" name="kivab_submit" value="productaddspec"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Add a new specification line to above product &raquo;', 'kivab').'"></input></p>';
				//echo "</div>";
				echo '</form>';
				

				
				// ////////////////////////////////////////////////////////// //
				// Product images creation
				// ////////////////////////////////////////////////////////// //
				
				$product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				//echo '<div class="wrap">';
				echo '<input type="hidden" name="kivab_submit" value="productaddimg"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Add a new image to above product &raquo;', 'kivab').'"></input></p>';
				//echo "</div>";
				echo '</form>';
				
				
				// ////////////////////////////////////////////////////////// //
				// Product videos creation
				// ////////////////////////////////////////////////////////// //
				
				$product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				//echo '<div class="wrap">';
				echo '<input type="hidden" name="kivab_submit" value="productaddvideo"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Add a new video to above product &raquo;', 'kivab').'"></input></p>';
				//echo "</div>";
				echo '</form>';
				
				// ////////////////////////////////////////////////////////// //
				// Product document attachment creation
				// ////////////////////////////////////////////////////////// //
				
				$product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				echo '<input type="hidden" name="kivab_submit" value="productadddoc"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Add a new attachment to above product &raquo;', 'kivab').'"></input></p>';
				echo '</form><br /><br />';
				

				echo '<hr />';
				
				// ////////////////////////////////////////////////////////// //
				// Category Table                                             //
				// ////////////////////////////////////////////////////////// //
				
				//check session scope for set category 
				
				
				$category_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM wp_kivab_categories"));
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				//echo '<div class="wrap">';
				echo '<input type="hidden" name="kivab_submit" value="productaddcategory"/>';
				echo '<p class="submit"><input type="submit" value="'.__('Add new category &raquo;', 'kivab').'"></input></p>';
				//echo "</div>";
				echo '</form>';
				
				// ////////////////////////////////////////////////////////// //
				// Category selection
				// ////////////////////////////////////////////////////////// //
				
				echo '<a name="cats"></a>';
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php#cats">';
				echo '<div class="wrap">';
				$myrows = $wpdb->get_results( "SELECT * FROM wp_kivab_categories");
				echo '<select name="selectcategory" onchange=\'this.form.submit()\'>';
				foreach ($myrows as $myrows) {
					echo '<option value=' . $myrows->catID . ''. ($myrows->catID == $_POST['selectcategory']?' SELECTED':'') . '>' . $myrows->categoryName. " | " . $myrows->catID;
				}
				echo '</select>';
				
				// SUBMIT
				echo '<input type="hidden" name="kivab_submit" value="productselect"></input>';
				echo '<span class="submit"><input type="submit" value="'.__('Select Category &raquo;', 'kivab').'"></input></span>';
				echo "</div>";
				echo '</form>';
				
				// ////////////////////////////////////////////////////////// //
				// Category Form
				// ////////////////////////////////////////////////////////// //
				if($currentCat != ''){
					$mycurrentcat= $wpdb->get_results( "SELECT * FROM wp_kivab_categories WHERE catID =" . $currentCat);
				}
				else{
						$mycurrentcat= $wpdb->get_results( "SELECT * FROM wp_kivab_categories WHERE catID =".intval($_POST['selectcategory']) );
						$currentCat = intval($_POST['selectcategory']);
				}
				
				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php#cats">';
				echo '<input type="hidden" name="kivab_submit" value="updatecategory"></input>';
					
					if($currentCat == ''){
						echo '<input type="hidden" name="catID" value="'. intval($_POST['selectcategory']).'" />';
					}
					else{
						echo '<input type="hidden" name="catID" value="'. intval($currentCat).'" />';
					}
				
				echo '<div class="wrap">';
				
				echo '<table class="form-table">';
				
				//echo $currentCat;
				
				echo '<tr><td>Category Name: </td><td>';
				echo '<input type="text" name="categoryName" value="'.$mycurrentcat[0]->categoryName.'" size="35"></input>';
				echo '</td></tr>';
				echo '<tr><td>Category Image URL: </td><td>';
				echo '<input type="text" name="categoryImg" value="'.$mycurrentcat[0]->categoryImg.'" size="35"></input>';
				echo '</td></tr>';
				
				echo '<tr><td>Category Order (1 being top): </td><td>';
				echo '<input type="text" name="sortOrder" value="'.$mycurrentcat[0]->sortOrder.'" size="5"></input> (Use 99 to hide in main menu)';
				echo '</td></tr>';
				
				// Description
				echo '<tr valign="top"><th scope="row">'.__('Category Description', 'kivab').'</th>';
				echo '<td>';
				echo '<textarea name="categoryDesc" rows=8 cols=80>'.$mycurrentcat[0]->categoryDesc.'</textarea>';
				echo '</td></tr>';
				
				$slider = $wpdb->get_results("SELECT * FROM wp_kivab_category_sliders WHERE catID = " .$mycurrentcat[0]->catID);
				
				//slider checkbox
				echo '<tr valign="top"><th scope="row">'.__('Category Slider', 'kivab').'</th>';
				echo '<td>';
				echo '<input type="checkbox" name="categorySlider" ';
				if($slider){
					echo 'checked';
					$slides = $wpdb->get_results("SELECT * FROM wp_kivab_category_slides WHERE sliderID = " .$slider[0]->sliderID. " ORDER BY slideOrder ASC");
				}
				echo ' >';
				echo '</td></tr>';
				
				//if slider, slides control
				if($slider){
					$i = 0;
					foreach($slides as $slides){
						if($i == 0){
						echo '<tr valign="top"><th scope="row">'.__('Category Slides', 'kivab').' <br /><span style="font-size:.8em">(to delete slide, delete slide content and update category)</span></th>';
						}
						else{
							echo '<tr valign="top"><th scope="row"></th>';
						}
						echo '<td>';
						$slideContent = stripslashes( $slides->slideContent );
						echo '<textarea name="slides['.$slides->slideID.'][slideContent]" rows=8 cols=80>'. stripslashes($slideContent) .'</textarea>';
						echo '</td></tr>';
						echo '<tr><td></td><td>';
						echo 'Slide Order: <input name="slides['.$slides->slideID.'][slideOrder]" type="text" value="'.$slides->slideOrder.'">';
						echo '<input name="slides['.$slides->slideID.'][slideID]" type="hidden" value="'.$slides->slideID.'">';
						echo '</td></tr>';
						$i++;
					}
				}
				
				//meta input. Needs Column, order, value and catID is auto. 
				
				if($slider){
					echo '<tr valign="top"><th scope="row">'.__('Category Meta', 'kivab').' <br /><span style="font-size:.8em">(to delete entry, delete entry content and update category)</span></th><th></th></tr>';
						
					$metaCols  = $wpdb->get_results("SELECT * FROM wp_kivab_category_meta WHERE catID = ". $currentCat . " GROUP BY metaColumn");
					foreach ($metaCols as $metaCols){
						echo '<tr valign="top"><th scope="row">'.$metaCols->metaColumn .'</th><td style="background-color:#cccccc">Do not use single quote!!! </td></tr><tr>';

						$meta = $wpdb->get_results("SELECT * FROM wp_kivab_category_meta WHERE catID = ". $currentCat . " AND metaColumn = '". $metaCols->metaColumn ."' ORDER BY metaOrder ASC");
						
						foreach($meta as $meta){
											
							echo '<tr><td></td><td>';
							$metaContent = stripslashes( $meta->content );
							echo '<input type="text" name="meta['.$meta->metaID.'][content]" value=\''. stripslashes($metaContent) .'\' size="100" />';
							echo '</td></tr>';
							echo '<tr><td></td><td>';
							echo 'Content Order: <input name="meta['.$meta->metaID.'][metaOrder]" type="text" value=\''.$meta->metaOrder.'\'>';
							echo '<input name="meta['.$meta->metaID.'][metaID]" type="hidden" value="'.$meta->metaID.'">';
							echo '</td></tr>';
							echo '<tr><td></td><td>';
							echo 'Column: <select name="meta['.$meta->metaID.'][metaColumn]">';
							
							echo '<option value = "benefits"';
								 if( $meta->metaColumn == "benefits") { echo "Selected"; }; 
							echo '>Benefits</option>';
							
							echo '<option value = "technology"';
							if( $meta->metaColumn == "technology") {
								echo "Selected";};
							echo '>Technology</option>';
							
							echo '<option value = "related products"';
							if( $meta->metaColumn == "related products") {
									echo "Selected";};
									echo '>Related Products</option>';
									
							//echo '<option value = "system diagram"';
							//if( $meta->metaColumn == "system diagram") {
								//echo "Selected";
							//};
							//echo '>System Diagram</option>';
									
							echo '</select>';
							echo '<input name="meta['.$meta->metaID.'][metaID]" type="hidden" value="'.$meta->metaID.'">';
							echo '</td></tr>';

						}
					}
					
					
				}
				//videos. Much like the products videos
				if($slider){
					$videos = $wpdb->get_results("SELECT * FROM wp_kivab_category_videos WHERE catID = ". $currentCat . " ORDER BY vidOrder ASC");
					$i = 0;
					foreach($videos as $videos){
						if($i == 0){
							echo '<tr valign="top"><th scope="row">'.__('Category Videos', 'kivab').' <br /><span style="font-size:.8em">(to delete video, delete video content and update category)</span></th>';
						}
						else{
							echo '<tr valign="top"><th scope="row"></th>';
						}
						echo '<td>';
						$vidContent = stripslashes( $videos->vidContent );
						echo '<textarea name="videos['.$videos->catVidID.'][vidContent]" rows=8 cols=80>'. stripslashes($vidContent) .'</textarea>';
						echo '</td></tr>';
						echo '<tr><td></td><td>';
						echo 'Video Order: <input name="videos['.$videos->catVidID.'][vidOrder]" type="text" value="'.$videos->vidOrder.'">';
						echo '</td></tr>';
						echo '<tr><td></td><td>';
						$vidURL = stripslashes($videos->vidURL);
						echo 'Please format url begining with http://www.youtube.com/watch?v=<br />';
						echo 'URL: <input name="videos['.$videos->catVidID.'][vidURL]" type="text" value="'.stripslashes($vidURL).'" size="75">';
						echo '</td></tr>';
						echo '<tr><td></td><td>';
						echo 'Title: <input name="videos['.$videos->catVidID.'][vidTitle]" type="text" value="'.stripslashes($videos->vidTitle).' " size="75">';
						echo '<input name="videos['.$videos->catVidID.'][catVidID]" type="hidden" value="'.$videos->catVidID.'">';
						echo '</td></tr>';
						$i++;
					}
				}
				
				echo '<tr><td> </td><td>';
				echo '<span class="submit"><input type="submit" value="'.__('Update Category &raquo;', 'kivab').'"></input></span>';
				echo '</td></tr>';
				echo '</table>';
				echo "</div>";
				echo '</form>';
				
				//new slide
				if($slider){
					echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php#cats">';
					echo '<input type="hidden" name="kivab_submit" value="insertSlide"></input>';
					echo '<input type="hidden" name="catID" value="'. intval($_POST['selectcategory']).'" />';
					echo '<input type="hidden" name="sliderID" value="'. intval($slider[0]->sliderID).'" />';
					echo '<input type="hidden" name="addSlide" value="true" />';
					echo '<span class="submit"><input type="submit" value="'.__('Add slide &raquo;', 'kivab').'"></input></span>';
				}
				echo '</form>';
				
				//new meta content
				if($slider){
					echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php#cats">';
					echo '<input type="hidden" name="kivab_submit" value="insertMeta"></input>';
					echo '<input type="hidden" name="catID" value="'. intval($currentCat).'" />';
					echo '<select name="metaColumn"><option value="technology">Technology</option><option value="benefits">Benefits</option><option value="related products">Related Products</option><option value="system diagram">System Diagram</option></select>';
					echo '<input type="hidden" name="insertMeta" value="true" />';
					echo '<span class="submit"><input type="submit" value="'.__('Add new meta &raquo;', 'kivab').'"></input></span>';
				}
				echo '</form>';
				
				//new category video 
				if($slider){
					echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php#cats">';
					echo '<input type="hidden" name="kivab_submit" value="insertVideo"></input>';
					echo '<input type="hidden" name="catID" value="'. intval($currentCat).'" />';
					echo '<input type="hidden" name="insertVideo" value="true" />';
					echo '<span class="submit"><input type="submit" value="'.__('Add video &raquo;', 'kivab').'"></input></span>';
				}
				echo '</form>';
				
				echo '<hr />';

				// ////////////////////////////////////////////////////////// //
				// Using Information
				// ////////////////////////////////////////////////////////// //

				echo '	<p><h2>'.__('Using kivab', 'kivab').'</h2></p>';
				echo '	<p>Create a Page or Post for your "Shopping Cart".</p>';
				echo '	<p>Add the tag [kivab &lt;categoryID&gt;] to the page or post to show the list of items in that category. You must know the numerical value of the category. For all categories use 0</p>';
				echo '	<p>The currently selected product will show up if you use the tag <b><i>[kivab '.$mycurrentprod[0]->category.']</i></b> :-)</p>';
				echo '</div>';

				echo '<hr />';

				// ////////////////////////////////////////////////////////// //
				// Options Form
				// ////////////////////////////////////////////////////////// //

				echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=kivab/kivab.php">';
				echo '<div class="wrap"><p><h2>'.__('Plugin Options', 'kivab').'</h2></p>';
				// settings
				echo '<table class="form-table">';
				// width
				echo '<tr valign="top"><th scope="row">'.__('Product Display Width', 'kivab').'</th>';
				echo '<td><input type="text" name="width" value="'.$options['width'].'" size="10"></input>&nbsp;'.__('Maximum width of pictures in pixels', 'kivab').'</td></tr>';
				// wptouchwidth
				echo '<tr valign="top"><th scope="row">'.__('Product Display Width in WPtouch', 'kivab').'</th>';
				echo '<td><input type="text" name="wptouchwidth" value="'.$options['wptouchwidth'].'" size="10"></input>&nbsp;'.__('Maximum width of pictures in WPtouch mode', 'kivab').'</td></tr>';
				// default picture
				echo '<tr valign="top"><th scope="row">'.__('Default Image URL', 'kivab').'</th>';
				echo '<td><input type="text" name="defimg" value="'.$options['defimg'].'" size="64"></input>&nbsp;'.__('Default image to show if product has no image.', 'kivab').'</td></tr>';
				// errors
				echo '<tr valign="top"><th scope="row">'.__('Show DB Errors', 'kivab').'</th>';
				if (isset($options['visibleerrors']) && !empty($options['visibleerrors'])) {
						$checked = " checked=\"checked\"";
				} else {
						$checked = "";
				}
				echo '<td><input type="checkbox" name="visibleerrors" value="on"'.$checked.' />&nbsp;'.__('Attempt to show all database related errors.', 'kivab').'</td></tr>';
				echo '</table>';

				// SUBMIT
				echo '<input type="hidden" name="kivab_submit" value="options"></input>';
				echo '<p class="submit"><input type="submit" value="'.__('Update Options &raquo;', 'kivab').'"></input></p>';
				echo "</div>";
				echo '</form>';

				echo '</div><hr />
				<style>
				#wpfooter{display:none;}
				</style>
				';

			} // End OptionPage()

			public static function PluginMenu() {
				add_options_page('kivab Plugin Options', 'Sonetics', 'manage_options', 'kivab/kivab.php', 'kivab::OptionPage');
			}


		} //End Class kivab

	} // End if (!class_exists("kivab"))


	// ////////////////////////////////////////////////////////// //
	// Kick-off: Load instance if class created correctly
	// ////////////////////////////////////////////////////////// //

	if (class_exists("kivab")) {
		$kivab_plugin = new kivab();
	}

?>