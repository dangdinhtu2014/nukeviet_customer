<?php

/**
 * @Project NUKEVIET 4.x
 * @Author DANGDINHTU (dlinhvan@gmail.com)
 * @Copyright (C) 2014 webdep24.com
 * @License GNU/GPL version 2 or any later version
 * @Createdate 24/06/2014 11:05
 */

if( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

if( ! nv_function_exists( 'content_slider_customer' ) )
{

	function nv_block_config_slider_customer_blocks( $module, $data_block, $lang_block )
	{
		global $db, $language_array, $site_mods;
		$html = '';
 
		$html .= '<tr>';
		$html .= '	<td>' . $lang_block['numrow'] . '</td>';
		$html .= '	<td><input type="text" name="config_numrow" size="5" value="' . $data_block['numrow'] . '" class="form-control"/></td>';
		$html .= '</tr>';
 		$html .= '<tr>';
		$html .= '	<td>' . $lang_block['random'] . '</td>';
		$html .= '	<td><input type="text" name="config_random" size="5" value="' . $data_block['random'] . '" class="form-control"/></td>';
		$html .= '</tr>';
 

		return $html;
	}

	function nv_block_config_slider_customer_blocks_submit( $module, $lang_block )
	{
		global $nv_Request;
		$return = array();
		$return['error'] = array();
		$return['config'] = array();
 		$return['config']['numrow'] = $nv_Request->get_int( 'config_numrow', 'post', 0 );
		$return['config']['random'] = $nv_Request->get_int( 'config_random', 'post', 0 );
 
		return $return;
	}

	function content_slider_customer( $block_config )
	{

		global $global_config, $module_info, $site_mods, $db, $module_name;
		$module = $block_config['module'];
		$mod_data = $site_mods[$module]['module_data'];
		$mod_file = $site_mods[$module]['module_file'];
		
		$block_config['numrow'] = ( $block_config['numrow'] != 0 ) ? $block_config['numrow'] : 10;

		$sql = 'SELECT photo_id, title, links, image, thumb FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_photo WHERE status = 1 ORDER BY weight ASC LIMIT 0 , ' . $block_config['numrow'];
 
		$array_photo = nv_db_cache( $sql, 'photo_id' . '_' . $block_config['numrow'], $module );
		
		if( !empty( $array_photo ) )
		{
			if( file_exists( NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $mod_file . '/block.slider.tpl' ) )
			{
				$block_theme = $module_info['template'];
			}
			else
			{
				$block_theme = 'default';
			}

			$xtpl = new XTemplate( 'block.slider.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/' . $mod_file . '' );

			$xtpl->assign( 'TEMPLATE', $block_theme );
			$xtpl->assign( 'MOD_FILE', $mod_file );
			$xtpl->assign( 'RANDOM', $block_config['random'] );
 			
			$a = 1;
			foreach( $array_photo as $data )
			{
				$data['image'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module . '/images/' . $data['image'];
				$data['thumb'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module . '/thumbs/' . $data['thumb'];
				$data['slide'] = $a;
				$xtpl->assign( 'DATA', $data );
				$xtpl->parse( 'main.loop' );
				++$a;
			}
			
			$array_photo = null;
			
			$xtpl->parse( 'main' );
			return $xtpl->text( 'main' );
		}
	}

}

if( defined( 'NV_SYSTEM' ) )
{
	global $site_mods, $module_name;
	$module = $block_config['module'];
	if( isset( $site_mods[$module] ) )
	{
		 
		$content = content_slider_customer( $block_config );
	}
}

