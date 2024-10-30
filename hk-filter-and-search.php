<?php
/*
Plugin Name: HTML filter and csv-file search
Plugin URI: http://wordpress.org/plugins/hk-filter-and-search
Description: Easy way to enable jquery HTML filter or a CSV-file-search to a webpage. Use the shortcodes [csvsearch] and [filtersearch] to enable.
Version: 2.8
Author: jonashjalmarsson
Author URI: https://jonashjalmarsson.se
License: GPLv3
Text Domain: hk-filter-and-search
Domain Path: /languages
*/

namespace hk_filter_and_search;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * load textdomain
 */
function hk_load_textdomain() {
	load_plugin_textdomain( 'hk-filter-and-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'init', __NAMESPACE__ . '\\hk_load_textdomain' );

/*
 * enqueue scripts
 */
function hk_filter_search_scripts() {
	if ( ! wp_script_is( 'jquery', 'enqueued' )) {
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\hk_filter_search_scripts' );

/*
 * shortcode [csvsearch], show search in csv-file function
 */
function hk_plugin_csv_search_shortcode( $atts, $content = null ) {
	
	$atts = shortcode_atts(
		array(
			'src' => '',
			'charset' => 'iso-8859-1',
			'format' => '{b}{0}{/b}, {1}, {2}{br/}',
			'searchtext' => __('Search', 'hk-filter-and-search'),
			'instantformat' => '{0}',
			'instantsearch' => 'false',
			'dataidformat' => "{0}",
			'csv_separator' => ";",
			'nothing_found_message' => __('Nothing found when searching for: ', 'hk-filter-and-search'),
			'placeholder_text' => '',
			'exact_match' => 'false',
			'only_search_in_column' => '-1',
			'show_header_row' => 'false',
			'skip_file_check' => 'false',
			'headerformat' => '',
			'ignore_default_header_style' => 'false',
			'set_focus_on_load' => 'false',
	), $atts );

	$atts = hk_escape_atts($atts);

	$src = $atts["src"];
	
	$html = "\n".'<!-- HK CSV search -->'."\n";
	$html .= "<div class='content-container  csv-container'>";

	$src = trim($src, '\'"”`´ ');

	if (empty($src)) {
		$html .= '<b>' . __('No file found or not a valid .csv file!', 'hk-filter-and-search') . '</b><br/>';
		$html .= '<b>' . __("Src has to be set and point to a .csv file.", 'hk-filter-and-search') . '</b><br/>';
		$html .= '</div><!-- END HK CSV search -->';
		return wp_kses_post($html);
	}

	$rand = rand(0,10000);
	$charset = $atts["charset"];
	$formatString = $atts["format"];
	$headerFormatString = $atts["headerformat"];
	if (empty($headerFormatString)) {
		$headerFormatString = $formatString;
	}
	$autofocus = ($atts['set_focus_on_load'] == 'true') ? 'autofocus ' : '';
	$set_focus_on_load = ($atts['set_focus_on_load'] == 'true') ? '$(".hk-csv-search-form-' . $rand . '").find(\'.hk-csv-input\').focus();' : '';
	$instantformatString = $atts["instantformat"];
	$search_text = $atts["searchtext"];
	$instantsearch = $atts["instantsearch"];
	$dataidformat = $atts["dataidformat"];
	$csv_separator = $atts["csv_separator"];
	$nothing_found_message = $atts["nothing_found_message"];
	$placeholder_text = $atts["placeholder_text"];
	$only_search_in_column = $atts["only_search_in_column"];
	$show_header_row = $atts["show_header_row"];
	$only_search_in_column = ctype_digit($only_search_in_column) ? intval($only_search_in_column) : -1;
	$skip_file_check = $atts["skip_file_check"];
	$ignore_default_header_style = $atts["ignore_default_header_style"];
	$pre_header_row = $post_header_row = '';
	if ($ignore_default_header_style == "false") {
		$pre_header_row = "<div class='hk_header_row'>";
		$post_header_row = '</div>';
	}
	
	

	$find_exact_match = ($atts["exact_match"] == 'true') ? 'true' : 'false';

	if (strlen($csv_separator) > 1) {
		$csv_separator = $csv_separator[0]; // get first character if long string
	}

	// check if file exists
	$site_url = site_url();
	$domain_name = $_SERVER['HTTP_HOST'];


	// get site path if any
	$site_path = str_replace($domain_name, '', $site_url);
	$site_path = str_replace('http://', '', $site_path);
	$site_path = str_replace('https://', '', $site_path);

	$upload_url = wp_upload_dir()['baseurl'] . '/';
	$upload_url =  str_replace($site_url, '', $upload_url);
	
	$upload_path = wp_upload_dir()['basedir'] . '/';
	$site_path =  str_replace($upload_url, '', $upload_path);
	
	$request_uri = $_SERVER['REQUEST_URI'];

	$src_without_site_url = str_replace($site_url, '', $src);
	
	if ($skip_file_check == "true") {
		// skip file check
	}
	else if (file_exists($site_path . $src)) {
		// all good
	}
	else if (file_exists($site_path . $src_without_site_url)) {
		$src = $src_without_site_url;
	}
	else {
		/* translators: %s: filename */
		$html .= __(sprintf("<b>File: %s not found.</b> Only support for local files.", $src), 'hk-filter-and-search') . '<br />';
		$html .= '</div><!-- END HK CSV search -->';
		return wp_kses_post($html);
	}

	$keep_writing = __('Keep writing...', 'hk-filter-and-search');

	$html .= '<div class="hk-csv-search-wrapper">';	
	$html .= '<form method="POST" class="hk-csv-search-form-'.$rand.'">';
	$html .= '<input type="text" name="hk-csv-input" class="hk-csv-input" ' . $autofocus . '/>';
	$html .= '<input type="submit" name="hk-csv-button" class="hk-csv-button" value="' . $search_text . '" />';
	if ($instantsearch == "true") {
		$html .= '<div class="hk-csv-instantsearch">'.$placeholder_text.'</div>';        
	}
	$html .= '<div class="hk-csv-search-output">'.$placeholder_text.'</div>';
	$html .= '</form></div>';
	$html .= '</div>'."\n";
	
	$style = '.hk_header_row { font-weight: bold } .hk-csv-instant-list { list-style-type: none; margin-left: 0; display: inline-block; border: 1px solid #ddd; } .hk-csv-instant-list li { padding: 4px; } .hk-csv-instant-list li:hover { background-color: #ddd; }';
	$js = '
		(function($) {
			show_header_row_' . $rand . ' = ' . $show_header_row . ';
			$(document).ready(function () {
				var lines' . $rand . ' = []; 
				var nothing_msg_' . $rand . ' = "' . $nothing_found_message . '";
				// search button
				$( ".hk-csv-search-form-' . $rand . '" ).submit(function( event ) {
					event.preventDefault();
                    var search = $(this).parents(".hk-csv-search-wrapper").find(".hk-csv-input").val();
				    if ($(this).parents(".hk-csv-search-wrapper").find(".hk-csv-instantsearch").length > 0) {
						search = $(this).parents(".hk-csv-search-wrapper").find(".hk-csv-instant-list li:first a").data("id");
                        if (search != "") {
							$(this).parents(".hk-csv-search-wrapper").find(".hk-csv-instantsearch").find("ul").remove();
                        }
                    }
                    
                    doSearch' . $rand . '(search);
				});
				
                '.$set_focus_on_load.'
				
                
                // on keyup
                //var timer;
                $(document).on("keyup", ".hk-csv-search-form-' . $rand . ' .hk-csv-input", function(event){
					//timer && clearTimeout(timer);
					//timer = setTimeout(instantSearch, 200);
                    instantSearch' . $rand . '($(this));
                });
				
                
                // on list click
                $(document).on("click", ".hk-csv-search-form-' . $rand . ' .hk-csv-instant-list li a", function(event){
					event.preventDefault();
					search = $(this).data("id");
					if (search != "") {
						$(this).parents("ul").remove();
					}
					
					doSearch' . $rand . '(search);
				});
					
					
				// do search
				function doSearch' . $rand . '(search) {
					if (search != "") {
						$(".hk-csv-search-form-' . $rand . ' .hk-csv-search-output").html("");
						var output = searchData' . $rand . '(search);
						
						if ((!show_header_row_' . $rand . ' && output.length == 0) || (show_header_row_' . $rand . ' && output.length <= 1)) {
							$(".hk-csv-search-form-' . $rand . ' .hk-csv-search-output").append(nothing_msg_' . $rand . ' + " " + search);
						}
						else {
							// output loop   
							for(i=0; i<output.length; i++){
								if (show_header_row_' . $rand . ' && i == 0) {
									retline = "' . $pre_header_row . $headerFormatString . $post_header_row . '";
								}
								else {
									retline = "' . $formatString . '";
								}
								
								for(c=0; c<output[i].length; c++) {
									retline = retline.replace("{"+c+"}",output[i][c]);
								}
								retline = retline.replace(/\{/g,"<");
								retline = retline.replace(/\}/g,">");
								$(".hk-csv-search-form-' . $rand . ' .hk-csv-search-output").append(retline);
							}
						}
                    }
                }
                
                
                // instant search
                function instantSearch' . $rand . '(event){
                    var search = $(event).parents(".hk-csv-search-wrapper").find(".hk-csv-input").val();
					var output = searchData' . $rand . '(search);
                    if (search.length < 3) {
                        $(event).parents(".hk-csv-search-wrapper").find(".hk-csv-instantsearch").html("<ul class=\'hk-csv-instant-list\'><li>' . $keep_writing . '</li></ul>");
                    }
                    else {
                        $(event).parents(".hk-csv-search-wrapper").find(".hk-csv-instantsearch").html("<ul class=\'hk-csv-instant-list\'>");
                        // output loop   
                        for(i=0; i<output.length && i<10; i++) {
                            retline = "' . $instantformatString . '";
                            dataid = "' . $dataidformat . '";
                            for(c=0; c<output[i].length; c++) {
                                retline = retline.replace("{"+c+"}",output[i][c]);
                                dataid = dataid.replace("{"+c+"}",output[i][c]);
                            }
                            
                            $(event).parents(".hk-csv-search-wrapper").find(".hk-csv-instant-list").append("<li><a href=\'#\' data-id=\'"+dataid+"\'>"+retline+"</a></li>");
                        }


                    }
                }

				// ajax call to get the data and store it in the var above
				$.ajax({
					type: "GET",
					url: "' . $src . '",
					dataType: "text",
					contentType: "Content-type: text/plain; charset=' . $charset. '",
					beforeSend: function(jqXHR) {
						jqXHR.overrideMimeType("text/html;charset=' . $charset. '");
					},
					success: function(data){
						processData' . $rand . '(data);
					}     
				});

				// function to process the data into an array
				function processData' . $rand . '(allText) {
					lines' . $rand . ' = allText.split(/\r\n|\n/);
				}


				// get first line function 
				function headerData' . $rand . '() {
					data = lines' . $rand . ';

					if (data.length > 0) {
						return data[0].split("' . $csv_separator . '");
					}
					else {
						return [];
					}
				}

				// search function 
				function searchData' . $rand . '(search){
					data = lines' . $rand . ';
					
					// Create a temp array to store the found data
					var tempArray = [];
					if (show_header_row_' . $rand . ') {
						var header = headerData' . $rand . '();
						tempArray.push(header);
					}
                    if (search != "")  {
                        // Loop through the data to see if a line has the search term we need
                        for(i=0; i<data.length; i++){
							if ('.$find_exact_match.') { // find exact match
								data[i].split("' . $csv_separator . '").forEach( function(item, index, arr) {
									if (item == search && (' . $only_search_in_column . ' == -1 || ' . $only_search_in_column . ' == index)) { // check exact name
										tempArray.push(arr);
										return;
									}
								});
							}
							else if (' . $only_search_in_column . ' != -1) {
								if (search !== undefined && search.toUpperCase !== undefined && $.isFunction(search.toUpperCase)) {
									search = search.toUpperCase();
								}
								data[i].split("' . $csv_separator . '").forEach( function(item, index, arr) {
									if (' . $only_search_in_column . ' == index) { // search only in column
										if (item.toUpperCase().indexOf(search) >= 0) {
											tempArray.push(arr);
											return;
										}
									}
								});
							}
							else { // find if only part of match
								if (search !== undefined && search.toUpperCase !== undefined && $.isFunction(search.toUpperCase)) {
									search = search.toUpperCase();
								}
								if (data[i].toUpperCase().indexOf(search) >= 0) {
									// Add found data to the array after split

									var line = data[i].split("' . $csv_separator . '"); 
									tempArray.push(line);
								}
							}
                        }
                    }
					// Return the array of matched data
					return tempArray;
				}

			 });

		})(jQuery);
		
	';
	
	return wp_kses(
			$html,
			array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'div' => array(
					'class' => array(),
				),
				'form' => array(
					'method' => array(),
					'class' => array(),
				),
				'input' => array(
					'type' => array(),
					'name' => array(),
					'class' => array(),
					'value' => array(),
				),
			) ) . 
		"<script>" . $js . "</script>\n" .
		"<style>" . esc_attr($style) . "</style>\n" .
		'<!-- END HK CSV search -->'."\n";
}


// shortcodes
/*
 * shortcode [filtersearch], show filter function
 */
function hk_plugin_filter_search_func( $atts ){
	global $post;

	$atts = shortcode_atts(
		array(
			'search_element' => 'table',
			'show_header_in_table' => 'false',
			'text' => __('Search on this site', 'hk-filter-and-search'),
			'clear_icon_class' => 'delete-icon',
			'clear_text' => '',
			'old_style' => 'false',
			'set_focus_on_load' => 'true',
		), $atts );
		
	$atts = hk_escape_atts($atts);

	$html = "";
	$rand = rand(0,10000);
	$old_style = ($atts["old_style"] == 'true') ? ' old' : '';
	$search_text = $atts["text"];
	
	$autofocus = ($atts['set_focus_on_load'] == 'true') ? 'autofocus ' : '';
	$set_focus_on_load = ($atts['set_focus_on_load'] == 'true') ? '$(parent).find(\'.filter.tool .filterinput\').focus();' : '';

	/* get html-element to filter */
	$filter_el = "";
	if ($atts["search_element"] != "") {
		$filter_el = $atts["search_element"];
	}
	$post_el = ".post-" . $post->ID;
	$page_el = ".page-id-" . $post->ID;

	if ($atts["show_header_in_table"] == "true") {
		$show_first_row = '
		if ($(parent).find("thead").length > 0) {
			$(parent).find("thead tr:first-child").show();
		}
		else {
			$(parent).find("tr:first-child").show();
		}
		';
	}
	$clear_icon_class = $atts["clear_icon_class"];
	$clear_text = $atts["clear_text"];
	
	$html .= "\n".'<!-- HK filter search -->'."\n";
	/* add search input area */
	$html .= "<div class='filtersearch$rand$old_style'>";
	$html .= "<div class='filter tool'>";
	$html .= "<span>" . $search_text . "</span>";
	$html .= "<input class='filterinput' type='text' name='filterinput' {$autofocus}/>";
	$html .= "<span class='" . $clear_icon_class . " rensa' style='margin:0'>" . $clear_text. "</span>";
	$html .= "</div>";
	$html .= "</div>";
	/* add default filter style */
	$style = '
		.filtersearch' . $rand . ' {
			background-color: #C6CACB;
			border-radius: 3px;
			display: block;
			padding: 12px;
			margin: 12px 0;
			width: 100%;
		}
		.old.filtersearch' . $rand . ' {
			float: left;
		}
		.filtersearch' . $rand . ':not(.old) .filter.tool {
			display: flex;
			flex-wrap: nowrap;
			flex-direction: row;
			align-content: center;
			justify-content: flex-start;
			align-items: center;
			column-gap: 0.5em;
		}
		.old.filtersearch' . $rand . ' .filter.tool span,
		.old.filtersearch' . $rand . ' .filter.tool input {
			float: left;
			margin-right: 12px;
		}
		.filtersearch' . $rand . ' .filter.tool .rensa {
			display: none; 
			cursor: pointer;
		';
	/* add the jquery script, the script uses random id to work even if added more than once in a page */
	$warning_text = __('Warning: Nothing to filter!', 'hk-filter-and-search');
	$js = '
		(function($) {
			/* case insensitive contain */
			$.extend($.expr[":"], {
			  "hk_containsi": function(elem, i, match, array) {
				return (elem.innerText.replace(\'\t\',\' \') || "").toLowerCase()
					.indexOf((match[3] || "").toLowerCase()) >= 0;
			  }
			});
			/* filter search function */
			var filter_search'.$rand.' = function(el) {
				var filter = $(el).val().toLowerCase();
				var parent = hk_filtersearch_get_parent'.$rand.'();
				

				// check if filter on element.class else filter on parent
				var filter_el = "' . $filter_el. '";
				// try to find in closest post/page parent if multiple elements found
				if ($(filter_el).length > 1) {
					if ($(parent).find(filter_el) !== undefined && $(parent).find(filter_el).length > 1) {
						selected_element = $(parent).find(filter_el);
					}
					else {
						selected_element = $(filter_el); // fallback on element
					}
				}
				else {
					selected_element = $(filter_el);
				}

				// Show warning if no element found
				if ($(selected_element) === undefined || $(selected_element).length == 0) {
					if($("#hk_filter_warning'.$rand.'").length > 0) {
						$("#hk_filter_warning'.$rand.'").html("<b>'.$warning_text.'</b>");
					}
					else {
						$(el).after("<p id=\"hk_filter_warning'.$rand.'\"><b>'.$warning_text.'</b></p>");
					}
				}

				// p-taggar
				if ($(selected_element).find("p").length > 0) {
					// show all
					$(selected_element).find("p").hide();
					// show contains
					$(selected_element).find("p:hk_containsi(\'"+filter+"\')").show();
				}
				// tr-taggar
				if ($(selected_element).find("tr").length > 0) {
					// show all
					$(selected_element).find("tr").hide();
					// show contains
					$(selected_element).find("tr td:hk_containsi(\'"+filter+"\')").parent().show();
				}

				// show if hidden by mistake filter
				$(selected_element).find("div.filtersearch'.$rand.'").show();
				' . $show_first_row . '
			
			} // end function filter_search
			
			var hk_filtersearch_get_parent'.$rand.' = function() {
				var parent = $("'. $post_el . '");
				
				// if no parent found try page id
				if ($(parent).length <= 0) {
					parent = $("'. $page_el . '");
				}

				// if no parent found try body
				if ($(parent).length <= 0) {
					parent = $(body);
				}
				return parent;
			};
			$(document).ready(function () {
				var parent = hk_filtersearch_get_parent'.$rand.'();
				/**
				 * filter functionality
				 */
				// check if filter exist
				if ($(parent).find(".filtersearch'. $rand .'").length <= 0) {
					return;
				}
				
				// add filter button
				$(parent).find(\'.filter.tool .rensa\').click(function() {
					$(this).parents(".filter.tool").find(\'.filterinput\').val(\'\');
					$(this).parents(".filter.tool").find(\'.rensa\').hide();
					$(this).parents(".filter.tool").find(\'.filterinput\').focus();
					filter_search'.$rand.'($(this));
				});		
				'.$set_focus_on_load.'
				$(parent).find(\'.filter.tool .filterinput\').keyup(function(ev) {
					$(this).parents(".filter.tool").find(\'.rensa\').show();
					filter_search'.$rand.'($(this));
				});
				/* end add filter input */
				
				
			});


		})(jQuery);
	';

	return wp_kses(
			$html,
			array(
				'span' => array(
					'class' => array(),
				),
				'div' => array(
					'class' => array(),
				),
				'input' => array(
					'type' => array(),
					'name' => array(),
					'class' => array(),
					'value' => array(),
				),
			) ) . 
		"<script>" . $js . "</script>\n" .
		"<style>" . esc_attr($style) . "</style>\n" .
		'<!-- END HK filter search -->'."\n";
}

add_action( 'init', __NAMESPACE__ . '\\hk_add_shortcode' );
function hk_add_shortcode() {
	add_shortcode( 'hk-filtersearch', __NAMESPACE__ . '\\hk_plugin_filter_search_func' );
	add_shortcode( 'filtersearch', __NAMESPACE__ . '\\hk_plugin_filter_search_func' ); // new shortcode
	add_shortcode( 'hk-csv-search', __NAMESPACE__ . '\\hk_plugin_csv_search_shortcode' );
	add_shortcode( 'csvsearch', __NAMESPACE__ . '\\hk_plugin_csv_search_shortcode' ); // new shortcode
}


function hk_escape_atts($atts) {
	foreach($atts as $key => $value) {
		if ($key == "src") {
			$atts[$key] = esc_url($value);
			// ignore src if not ending with .csv
			if (substr($atts[$key], -4) != ".csv") {
				$atts[$key] = "";
			}
		}
		else {
			$atts[$key] = esc_attr($value);
		}
	}
	return $atts;
}


