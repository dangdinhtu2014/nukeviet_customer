<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Tue, 10 Jun 2014 02:22:18 GMT
 */

if( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );

$page_title = $lang_module['photo'];
 
 
if( ACTION_METHOD == 'status' )
{
	$photo_id = $nv_Request->get_int( 'photo_id', 'post', 0 );
	$mod = $nv_Request->get_string( 'action', 'get,post', '' );
	$new_vid = $nv_Request->get_int( 'new_vid', 'post', 0 );
	$content = 'NO_' . $photo_id;

	list( $photo_id ) = $db->query( 'SELECT photo_id FROM ' . TABLE_CUSTOMER_NAME . '_photo WHERE photo_id=' . $photo_id )->fetch( 3 );
	if( $photo_id > 0 )
	{
		if( $mod == 'status' and ( $new_vid == 0 or $new_vid == 1 ) )
		{
			$sql = 'UPDATE ' . TABLE_CUSTOMER_NAME . '_photo SET status=' . $new_vid . ' WHERE photo_id=' . $photo_id;
			$db->query( $sql );

			$content = 'OK_' . $photo_id;
		}
 
		nv_del_moduleCache( $module_name );
	}
	echo $content;
	exit();

}

if( ACTION_METHOD == 'weight' )
{
	$photo_id = $nv_Request->get_int( 'photo_id', 'post', 0 );
	$mod = $nv_Request->get_string( 'action', 'get,post', '' );
	$new_vid = $nv_Request->get_int( 'new_vid', 'post', 0 );
	$content = 'NO_' . $photo_id;
 	
	if( empty( $new_vid ) ) die( 'NO_' . $mod );

	$sql = 'SELECT photo_id FROM ' . TABLE_CUSTOMER_NAME . '_photo WHERE photo_id=' . $photo_id;
	$photo_id = $db->query( $sql )->fetchColumn();
	if( empty( $photo_id ) ) die( 'NO_' . $photo_id );


	$sql = 'SELECT photo_id FROM ' . TABLE_CUSTOMER_NAME . '_photo WHERE photo_id!=' . $photo_id . ' ORDER BY weight ASC';
	$result = $db->query( $sql );

	$weight = 0;
	while( $row = $result->fetch() )
	{
		++$weight;
		if( $weight == $new_vid ) ++$weight;

		$sql = 'UPDATE ' . TABLE_CUSTOMER_NAME . '_photo SET weight=' . $weight . ' WHERE photo_id=' . $row['photo_id'];
		$db->query( $sql );
	}

	$sql = 'UPDATE ' . TABLE_CUSTOMER_NAME . '_photo SET weight=' . $new_vid . ' WHERE photo_id=' . $photo_id;
	$db->query( $sql );

	nv_del_moduleCache( $module_name );
	
	$content = 'OK_' . $photo_id; 
	
	echo $content;
	exit();

}

if( ACTION_METHOD == 'delete' )
{
	$info = array();
	$photo_id = $nv_Request->get_int( 'photo_id', 'post', 0 );
	$token = $nv_Request->get_title( 'token', 'post', '' );
	
	$listid = $nv_Request->get_string( 'listid', 'post', '' );
	if( $listid != '' and md5( $global_config['sitekey'] . session_id() ) == $token )
	{
		$del_array = array_map( 'intval', explode( ',', $listid ) );
	}
	elseif( $token == md5( $global_config['sitekey'] .  session_id() . $photo_id ) )
	{
		$del_array = array( $photo_id );
	}
 
	if( ! empty( $del_array ) )
	{
		$a = 0;
		foreach( $del_array as $photo_id )
		{
			$photo = $db->query( 'SELECT * FROM ' . TABLE_CUSTOMER_NAME . '_photo WHERE photo_id=' . (int)$photo_id )->fetch();
	
			$delete = $db->prepare('DELETE FROM ' . TABLE_CUSTOMER_NAME . '_photo WHERE photo_id=' . (int)$photo['photo_id'] );
			$delete->execute();
			
			if( $delete->rowCount() )
			{ 		
				
				$info['id'][$a] = $photo_id;
 
				++$a;
			}
 	
		}
		if( !empty( $a ) )
		{
			$info['success'] = $lang_module['photo_success_delete'] ;
		}
		
	}else
	{
		$info['error'] = $lang_module['photo_error_delete'];
	}
	echo json_encode( $info );
	exit();
}
  
 
if( ACTION_METHOD == 'add' || ACTION_METHOD == 'edit' )
{
	
 
	$data = array(
		'photo_id' => 0,
		'title' => '',
		'links' =>'',
		'image' =>'',
		'thumb' =>'',
		'status' => 1,
		'date_added' => NV_CURRENTTIME
	);
	 
	$error = array();
 
	$data['photo_id'] = $nv_Request->get_int( 'photo_id', 'get,post', 0 );
 	if( $data['photo_id'] > 0 )
	{
		$data = $db->query( 'SELECT *
		FROM ' . TABLE_CUSTOMER_NAME . '_photo  
		WHERE photo_id=' . $data['photo_id'] )->fetch();
 
		$caption = $lang_module['photo_edit'];
	}
	else
	{
		$caption = $lang_module['photo_add'];
	}

	if( $nv_Request->get_int( 'save', 'post' ) == 1 )
	{

		$data['photo_id'] = $nv_Request->get_int( 'photo_id', 'post', 0 );
 		$data['title'] = nv_substr( $nv_Request->get_title( 'title', 'post', '', '' ), 0, 255 );
 		$data['links'] = nv_substr( $nv_Request->get_title( 'links', 'post', '', '' ), 0, 255 );
		$data['status'] = $nv_Request->get_int( 'status', 'post', 0 );
		
		$image = $nv_Request->get_string( 'image', 'post', '' );
		if( is_file( NV_DOCUMENT_ROOT . $image ) )
		{
			$lu = strlen( NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/images/' );
			$data['image'] = substr( $image, $lu );
		}
		else
		{
			$data['image'] = '';
		}
		 
		
		if( empty( $data['title'] ) )
		{
			$error['title'] = $lang_module['photo_error_title'];	
		}
		if( empty( $data['links'] ) || ! nv_is_url( $data['links'] ) )
		{
			$error['links'] = $lang_module['photo_error_links'];	
		}
		if( ! empty( $error ) && ! isset( $error['warning'] ) )
		{
			$error['warning'] = $lang_module['photo_error_warning'];
		}
 
		if( empty( $error ) )
		{
 
			if( !empty( $data['image'] ) )
			{
				$width = 109;
				$height = 54;
				$image = NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_name . '/images/' . $data['image'];
				$imginfo = nv_is_image( $image );

				if( $width >= $height ) $rate = $width / $height;
				else  $rate = $height / $width;
				$basename = preg_replace( '/(.*)(\.[a-zA-Z]+)$/', '\1-thumb\2',  basename( $data['image'] ) );
		 
				require_once NV_ROOTDIR . '/includes/class/image.class.php';
				$createImage = new image(  $image , NV_MAX_WIDTH, NV_MAX_HEIGHT );
				if( $imginfo['width'] <= $imginfo['height'] )
				{
					$createImage->resizeXY( $width, 0 );

				}
				elseif( ( $imginfo['width'] / $imginfo['height'] ) < $rate )
				{
					$createImage->resizeXY( $width, 0 );
				}
				elseif( ( $imginfo['width'] / $imginfo['height'] ) >= $rate )
				{
					$createImage->resizeXY( 0, $height );
				}
				$createImage->cropFromCenter( $width, $height );
				$createImage->save( NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_name . '/thumbs', $basename );
				$createImage->close();
 
				$data['thumb'] = $basename;
				
			}
		
			if( $data['photo_id'] == 0 )
			{
				$weight = $db->query( 'SELECT MAX(weight) FROM ' . TABLE_CUSTOMER_NAME . '_photo' )->fetchColumn();
				$data['weight'] = intval( $weight ) + 1;
				
				$stmt = $db->prepare( 'INSERT INTO ' . TABLE_CUSTOMER_NAME . '_photo SET 
					status=' . intval( $data['status'] ) . ', 
					weight=' . intval( $data['weight'] ) . ', 
					date_added=' . intval( $data['date_added'] ) . ',  
					title =:title,
					links =:links,
					image =:image,
					thumb =:thumb' );
					
				$stmt->bindParam( ':title', $data['title'], PDO::PARAM_STR );
				$stmt->bindParam( ':links', $data['links'], PDO::PARAM_STR );
  				$stmt->bindParam( ':image', $data['image'], PDO::PARAM_STR );
  				$stmt->bindParam( ':thumb', $data['thumb'], PDO::PARAM_STR );
				$stmt->execute();

				if( $data['photo_id'] = $db->lastInsertId() )
				{
					 	
					
					nv_insert_logs( NV_LANG_DATA, $module_name, 'Add A photo', 'photo_id: ' . $data['photo_id'], $admin_info['userid'] );	 

				}
				else
				{
					$error['warning'] = $lang_module['photo_error_save'];

				}
				$stmt->closeCursor();

			}
			else
			{
				
				try
				{
						
					$stmt = $db->prepare( 'UPDATE ' . TABLE_CUSTOMER_NAME . '_photo SET 
						status=' . intval( $data['status'] ) . ', 
						weight=' . intval( $data['weight'] ) . ', 
						date_added=' . intval( $data['date_added'] ) . ',  
						title =:title,
						links =:links,
						image =:image,
						thumb =:thumb
						WHERE photo_id=' . $data['photo_id'] );
 
						
					$stmt->bindParam( ':title', $data['title'], PDO::PARAM_STR );
					$stmt->bindParam( ':links', $data['links'], PDO::PARAM_STR );
					$stmt->bindParam( ':image', $data['image'], PDO::PARAM_STR );
					$stmt->bindParam( ':thumb', $data['thumb'], PDO::PARAM_STR );
		 
					if( $stmt->execute() )
					{
 
						nv_insert_logs( NV_LANG_DATA, $module_name, 'Edit A photo', 'photo_id: ' . $data['photo_id'], $admin_info['userid'] );
						
					}
					else
					{
						$error['warning'] = $lang_module['photo_error_save'];

					}

					$stmt->closeCursor();

				}
				catch ( PDOException $e )
				{ 
					$error['warning'] = $lang_module['photo_error_save'];
					// var_dump($e);
				}

			}

		}
		
		if( empty( $error ) )
		{
			nv_del_moduleCache( $module_name );
			Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=photo' );
			die();
		}

	}

	if( ! empty( $data['image'] ) and file_exists( NV_UPLOADS_REAL_DIR . '/' . $module_name . '/images/' . $data['image'] ) )
	{
		$data['image'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/images/' . $data['image'];
	}

	$xtpl = new XTemplate( 'photo_add.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file );
	$xtpl->assign( 'LANG', $lang_module );
	$xtpl->assign( 'GLANG', $lang_global );
	$xtpl->assign( 'NV_LANG_VARIABLE', NV_LANG_VARIABLE );
	$xtpl->assign( 'NV_LANG_DATA', NV_LANG_DATA );
	$xtpl->assign( 'NV_BASE_SITEURL', NV_BASE_SITEURL );
	$xtpl->assign( 'THEME', $global_config['site_theme'] );
	$xtpl->assign( 'NV_BASE_ADMINURL', NV_BASE_ADMINURL );
	$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );
	$xtpl->assign( 'NV_OP_VARIABLE', NV_OP_VARIABLE );
	$xtpl->assign( 'MODULE_FILE', $module_file );
	$xtpl->assign( 'MODULE_NAME', $module_name );
	$xtpl->assign( 'OP', $op );
	$xtpl->assign( 'CAPTION', $caption );
	$xtpl->assign( 'DATA', $data );
	$xtpl->assign( 'UPLOAD_CURRENT', NV_UPLOADS_DIR . '/' . $module_name . '/images' );
	$xtpl->assign( 'UPLOAD_PATH', NV_UPLOADS_DIR . '/' . $module_name );
	$xtpl->assign( 'CANCEL', NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op );
 
	if( isset( $error['warning'] ) )
	{
		$xtpl->assign( 'error_warning', $error['warning'] );
		$xtpl->parse( 'main.error_warning' );
	}
 
 
	if( isset( $error['title'] ) )
	{
		$xtpl->assign( 'error_title', $error['title'] );
		$xtpl->parse( 'main.error_title' );
	}
	if( isset( $error['links'] ) )
	{
		$xtpl->assign( 'error_links', $error['links'] );
		$xtpl->parse( 'main.error_links' );
	}
  
	foreach( $array_status as $key => $name )
	{
		$xtpl->assign( 'STATUS', array( 'key'=> $key, 'name'=> $name, 'selected'=> ( $key == $data['status'] ) ? 'selected="selected"' : '' ) );
		$xtpl->parse( 'main.status' );
	}
  
	$xtpl->parse( 'main' );
	$contents = $xtpl->text( 'main' );
	include NV_ROOTDIR . '/includes/header.php';
	echo nv_admin_theme( $contents );
	include NV_ROOTDIR . '/includes/footer.php';

	exit();
}


if( ACTION_METHOD == 'get_photo' )
{
	$title = $nv_Request->get_string( 'filter_title', 'get', '' );
	$info = array();

	$and = '';
	if( ! empty( $title ) )
	{
		$and .= ' AND title LIKE :title ';
	}

	$sql = 'SELECT photo_id, title FROM ' . TABLE_CUSTOMER_NAME . '_photo  
	WHERE 1 ' . $and . '
	ORDER BY title DESC LIMIT 0, 10';

	$sth = $db->prepare( $sql );

	if( ! empty( $title ) )
	{
		$sth->bindValue( ':title', '%' . $title . '%' );
	}
	$sth->execute();
	while( list( $photo_id, $title ) = $sth->fetch( 3 ) )
	{
		$info[] = array( 'photo_id' => $photo_id, 'title' => nv_htmlspecialchars( $title ) );
	}
	header( 'Content-Type: application/json' );
	echo json_encode( $info );
	exit();
}

/*show list photo*/

$per_page = 50;

$page = $nv_Request->get_int( 'page', 'get', 1 );

$data['filter_status'] = $nv_Request->get_string( 'filter_status', 'get', '' );
$data['filter_title'] = strip_tags( $nv_Request->get_string( 'filter_title', 'get', '' ) );
$data['filter_date_added'] = $nv_Request->get_string( 'filter_date_added', 'get', '' );
 
$sort = $nv_Request->get_string( 'sort', 'get', '' );
$order = $nv_Request->get_string( 'order', 'get' ) == 'desc' ? 'desc' : 'asc';
 
 
$sql = TABLE_CUSTOMER_NAME . '_photo WHERE 1';
 
if( ! empty( $data['filter_title'] ) )
{
	$sql .= " AND title LIKE '" . $db->dblikeescape( $data['filter_title'] ) . "%'";
}
 
 
if( isset( $data['filter_status'] ) && is_numeric( $data['filter_status'] ) )
{
	$sql .= " AND status = " . ( int )$data['filter_status'];
}

if( preg_match( '/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $data['filter_date_added'], $m ) )
{
	$date_added_start = mktime( 0, 0, 0, $m[2], $m[1], $m[3] );
	$date_added_end = $date_added_start + 86399;

	$sql .= " AND date_added BETWEEN " . $date_added_start . " AND " . $date_added_end . "";
}
$sort_data = array( 'title',  'date_added' );
if( isset( $sort ) && in_array( $sort, $sort_data ) )
{

	$sql .= " ORDER BY " . $sort;
}
else
{
	$sql .= " ORDER BY weight";
}

if( isset( $order ) && ( $order == 'desc' ) )
{
	$sql .= " DESC";
}
else
{
	$sql .= " ASC";
}


 
$num_items = $db->query( 'SELECT COUNT(*) FROM ' . $sql )->fetchColumn();

$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=photo&amp;sort=' . $sort . '&amp;order=' . $order . '&amp;per_page=' . $per_page;

$db->sqlreset()->select( '*' )->from( $sql )->limit( $per_page )->offset( ( $page - 1 ) * $per_page );
 
$result = $db->query( $db->sql() );

$array = array();
while( $rows = $result->fetch() )
{
	$array[] = $rows;
}

$xtpl = new XTemplate( 'photo.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file );
$xtpl->assign( 'LANG', $lang_module );
$xtpl->assign( 'NV_LANG_VARIABLE', NV_LANG_VARIABLE );
$xtpl->assign( 'NV_LANG_DATA', NV_LANG_DATA );
$xtpl->assign( 'NV_BASE_SITEURL', NV_BASE_SITEURL );
$xtpl->assign( 'THEME', $global_config['site_theme'] );
$xtpl->assign( 'NV_BASE_ADMINURL', NV_BASE_ADMINURL );
$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );
$xtpl->assign( 'NV_OP_VARIABLE', NV_OP_VARIABLE );
$xtpl->assign( 'OP', $op );
$xtpl->assign( 'MODULE_FILE', $module_file );
$xtpl->assign( 'MODULE_NAME', $module_name );
$xtpl->assign( 'DATA', $data );
$xtpl->assign( 'TOKEN', md5( $global_config['sitekey'] . session_id() ) );
$xtpl->assign( 'URL_SEARCH', NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&action=get_photo' );

$order2 = ( $order == 'asc' ) ? 'desc' : 'asc';
$xtpl->assign( 'URL_TITLE', NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;sort=title&amp;order=' . $order2 . '&amp;per_page=' . $per_page );
$xtpl->assign( 'URL_WEIGHT', NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;sort=weight&amp;order=' . $order2 . '&amp;per_page=' . $per_page );
 
$xtpl->assign( 'ADD_NEW', NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op . "&action=add" );
 
/*search*/
 
foreach( $array_status as $key => $name )
{
	$xtpl->assign( 'STATUS', array( 'key'=> $key, 'name'=> $name, 'selected'=> ( $key == $data['filter_status'] && is_numeric( $data['filter_status'] ) ) ? 'selected="selected"': '' ) );
	$xtpl->parse( 'main.filter_status' );
}
 
 
if( ! empty( $array ) )
{
	foreach( $array as $item )
	{
 

  		$item['date_added'] = nv_date( 'd/m/Y', $item['date_added'] );
		$item['token'] = md5( $global_config['sitekey'] . session_id() . $item['photo_id'] );
		$item['image']  = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/images/' . $item['image'];
		$item['thumb']  = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/thumbs/' . $item['thumb'];
		$item['add'] = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=photo&action=add&token=" . $item['token'] . "&photo_id=" . $item['photo_id'];
 		$item['edit'] = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=photo&action=edit&token=" . $item['token'] . "&photo_id=" . $item['photo_id'];

 
		$xtpl->assign( 'LOOP', $item );
		
		foreach( $array_status as $key => $name )
		{
			$xtpl->assign( 'STATUS', array( 'key'=> $key, 'name'=> $name, 'selected'=> ( $key == $item['status'] ) ? 'selected="selected"': '' ) );
			 $xtpl->parse( 'main.loop.status' );
		}
		for( $i = 1; $i <= $num_items; ++$i )
		{
			$xtpl->assign( 'WEIGHT', array(
				'key' => $i,
				'name' => $i,
				'selected' => ( $i == $item['weight'] ) ? ' selected="selected"' : ''
			) );

			$xtpl->parse( 'main.loop.weight' );
		}

		$xtpl->parse( 'main.loop' );
	}

}
 
$generate_page = nv_generate_page( $base_url, $num_items, $per_page, $page );
if( ! empty( $generate_page ) )
{
	$xtpl->assign( 'GENERATE_PAGE', $generate_page );
	$xtpl->parse( 'main.generate_page' );
}

$xtpl->parse( 'main' );
$contents = $xtpl->text( 'main' );
include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';
