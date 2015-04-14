<?php
/*
Plugin Name: Lesson Scheduler
Plugin URI: 
Description: Just another lesson schedule manegement plugin. Simple UI and look.
Author: Teruo Morimoto
Author URI: http://stepxstep.net/
Version: 1.1.8
*/

/*  Copyright 2013 Teruo Mormoto (email : terusun at gmail.com)

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
	//設定画面処理用ファイル読み込み
	include_once dirname( __FILE__ ) . '/lesson_scheduler_options.php';

	//モバイル処理用ファイル読み込み
	include_once dirname( __FILE__ ) . '/lesson_scheduler_mobile.php';

/*  カスタム投稿タイプ（lesson scheduler）の登録
-----------------------------------------------------------*/
add_action('init', 'create_lesson_schedules');
function create_lesson_schedules(){

	$labels = array(
		'name' =>  _x('lesson schedules','lesson schedules'),
		'singular_name' =>  _x('lesson schedule','lesson schedule'),
		'add_new' =>  _x('Add New', 'post'),
		'add_new_item' =>  _x('Add New Post', 'Add New Post'),
		'edit_item' => _x('Edit Post','Edit Post'),
		'new_item' => _x('New Post','New Post'),
		'view_item' => _x('View Post','View Post'),
		'search_items' => _x('Search Posts','Search Posts') 
	);
	
	//タイトル自動化の場合
	if( get_option('lesson_scheduler_cb_1') == '1' ){
	    $args = array(
	    	'labels' => $labels,
	        'public' => true,
	        'capability_type' => 'post',
	        'hierarchical' => false,	
	        'has_archive' => true,		
	        'supports' => array(
	            'slug'
	        ),
	        'register_meta_box_cb' => 'lesson_schedules_meta_box'     // カスタムフィールドを使う
	    );
	}
	else{
	    $args = array(
	    	'labels' => $labels,
	        'public' => true,
	        'capability_type' => 'post',
	        'hierarchical' => false,	
	        'has_archive' => true,		
	        'supports' => array(
	            'title'
	        ),
	        'register_meta_box_cb' => 'lesson_schedules_meta_box'     // カスタムフィールドを使う
	    );
	}
	/*

	    $args = array(
	    	'labels' => $labels,
	        'public' => true,
	        'capability_type' => 'post',
	        'hierarchical' => false,	
	        'has_archive' => true,		
	        'supports' => array(
	            'slug'
	        ),
	        'register_meta_box_cb' => 'lesson_schedules_meta_box'     // カスタムフィールドを使う
	    );
	*/
    register_post_type('lesson_schedules', $args);
    
}

/* カスタム投稿で使用するカスタムフィールドの登録
-----------------------------------------------------------*/
function lesson_schedules_meta_box($post){
    add_meta_box(
        'lesson_schedule_meta',
        _x('Setting lesson Schedule','Setting lesson Schedule'),
        'lesson_schedule_meta_callback',
        'lesson_schedules',
        'normal',
        'high'
    );
}

/* カスタムフィールドの画面設定
-----------------------------------------------------------*/
function lesson_schedule_meta_callback($post, $box){

    // カスタムフィールドの値を取得
    $field1 = get_post_meta($post->ID, 'lesson_schedule_field1', true);
    $field2 = get_post_meta($post->ID, 'lesson_schedule_field2', true);
    $field3 = get_post_meta($post->ID, 'lesson_schedule_field3', true);
    $field4 = get_post_meta($post->ID, 'lesson_schedule_field4', true);
    
    echo wp_nonce_field('lesson_schedule_meta', 'lesson_schedule_meta_nonce');

	//練習日設定画面作成
	// datapickerによるカレンダー表示
	echo "<p>";
	echo _e('lesson date','lesson-scheduler').":";
	echo "<input type='text' id='lesson_datepicker' name='lesson_schedule_field1' value='";
	echo $field1;
	echo "'/></p>";
	
	echo "<p>";
	echo _e('lesson place','lesson-scheduler').":";
	echo "</p>";

	//練習場所設定用ラジオボタン作成
	for( $i=1; $i<=10; $i++ ){
		$optname = "lesson_scheduler_place_".$i;
		$optval = get_option($optname);
		if( $optval != "" ){
			$selected = ( $optval == $field2 ) ? "checked" : "";
			echo "<p><input type='radio' name='lesson_schedule_field2' value='".$optval."' ".$selected." >".$optval."</input></p>";
		}
	}
	
	echo "<p>";
	echo _e('lesson time','lesson-scheduler').":";
	echo "</p>";

	//練習時間設定用ラジオボタン作成
	for( $i=1; $i<=10; $i++ ){
		$optname = "lesson_scheduler_time_".$i;
		$optval = get_option($optname);
		if( $optval != "" ){
			$selected = ( $optval == $field3 ) ? "checked" : "";
			echo "<p><input type='radio' name='lesson_schedule_field3' value='".$optval."' ".$selected." >".$optval."</input></p>";
		}
	}

	//備考設定用テキストボックス作成
	echo "<p>";
	echo _e('remarks','lesson-scheduler').":";
	echo "<input type='text' name='lesson_schedule_field4' value='";
	echo $field4;
	echo "' size='100' /></p>";
	
}

/* カスタムフィールドの保存処理
-----------------------------------------------------------*/
add_action('save_post', 'lesson_schedule_meta_update');
function lesson_schedule_meta_update($post_id){

    if (!wp_verify_nonce( $_POST['lesson_schedule_meta_nonce'], 'lesson_schedule_meta')) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    
    //カスタム投稿をチェック
    if ('lesson_schedules' == $_POST['post_type']) {
        if(!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    } else {
        return $post_id;
    }
    
	//各種カスタムフィールド値を更新
	for( $i=1; $i<=4; $i++ ){
	
		$fieldname = "lesson_schedule_field".$i;
	
	    $field = $_POST[$fieldname];
	    if($field == '') {
	        delete_post_meta($post_id, $fieldname);
	    }
	    else {
	        update_post_meta($post_id, $fieldname, $field);
	    }
	}

}

/* 表示用ショートコード[lesson scheduler]対応処理
-----------------------------------------------------------*/
add_shortcode('lesson scheduler', 'disp_lesson_scheduler');
function disp_lesson_scheduler($atts) {
	if( lesson_scheduler_chk_mobile() ){
		disp_lesson_scheduler_mobile();
	}
	else{
		disp_lesson_scheduler_pc();
	}
}
function disp_lesson_scheduler_pc(){
	
	//クエリーによりカスタム投稿読み込み
	
	//1度に10件表示
	$lesson_schedule_per_page = 10;
	
	//複数ページの場合に、選択されたページを取得
	$paged = get_query_var('paged');
    //過去ページを出さない場合は、昇順ソード
/*    
    if( strcmp(get_option('lesson_scheduler_cb_2'),'1') != 0 ){
        $today = date('Ymd');
        $where = 'order=ASC';
    }
    else{
        $where = 'order=DESC';
    }
        
	query_posts( "posts_per_page=$lesson_schedule_per_page&paged=$paged&post_type=lesson_schedules&orderby=concat(right(meta_value,4),left(meta_value,2),mid(meta_value,4,2))&meta_key=lesson_schedule_field1&$where" );
*/
	//1度に10件表示
	$lesson_schedule_per_page = 10;
	
	//複数ページの場合に、選択されたページを取得
	$paged = get_query_var('paged');
	query_posts( "posts_per_page=$lesson_schedule_per_page&paged=$paged&post_type=lesson_schedules&orderby=meta_value&meta_key=lesson_schedule_field1" );
    
	global $wp_query;
	
	//記事があれば投稿データをロード（表示はしない） 
    /*
 	if ( have_posts() ){ 
		the_post(); 
	}
	else{
		echo _e('NOT FOUND','lesson-scheduler');
	}

	/* 投稿を巻き戻し *//*
	rewind_posts();
	*/
?>
<div class="lesson_scheduler" >
    
<?php
//自分自身のURLを取得する
if ( isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on' ){  
    $protocol = 'https://';  
}  
else{  
    $protocol = 'http://';  
}  
$myurl  = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
?>

<form action="<?php echo $myurl; ?>" method="POST">
<?php if(  is_user_logged_in() ) : ?>
	<h3><?php _e('your status','lesson-scheduler'); ?></h3>
<?php else : ?>
	<h3><?php _e('schedule','lesson-scheduler'); ?></h3>
<?php endif; ?>
<div class="tablelesson-2">
	<table class="lesson_scheduler_table">
		<!-- タイトル行の表示 -->
		<thead>
		<?php if(  is_user_logged_in() ) : ?>
			<tr><th><?php _e('lesson date','lesson-scheduler') ?></th><th><?php _e('lesson place','lesson-scheduler') ?></th><th><?php _e('lesson time','lesson-scheduler') ?></th><th><?php _e('remarks','lesson-scheduler') ?></th>
			<th><?php _e('reply','lesson-scheduler') ?></th><th><?php _e('comment','lesson-scheduler') ?></th></tr>
		<?php else :?>
			<tr><th><?php _e('lesson date','lesson-scheduler') ?></th><th><?php _e('lesson place','lesson-scheduler') ?></th><th><?php _e('lesson time','lesson-scheduler') ?></th><th><?php _e('remarks','lesson-scheduler') ?></th></tr>
		<?php endif; ?>
		</thead>
		
		<?php
			// 練習分ループ 
			while ( have_posts() ) : the_post(); ?>
			<div>
			
				<?php
					$cu = wp_get_current_user();

					//送信ボタンが押されたかつ、その時のIDと同一ならば登録
					if ($_POST['syuketu'.get_the_ID()] != '' && strcmp( $_POST['id'.get_the_ID()], get_the_ID()) == 0 ) {
						delete_post_meta( get_the_ID(),  $cu->user_login ); 
						update_post_meta( get_the_ID(),  $cu->user_login, $_POST['syuketu'.get_the_ID()]);
						delete_post_meta( get_the_ID(),  $cu->user_login."1" ); 
						update_post_meta( get_the_ID(),  $cu->user_login."1", $_POST['comment'.get_the_ID()]);
					}
				?>

				<!-- タイトルの表示 -->
				<?php 

					//練習日を取得
				    $lesson_date = get_post_custom_values('lesson_schedule_field1');
				    if( $lesson_date  ){

						//過去の練習を出さない場合はチェックする
						if( strcmp(get_option('lesson_scheduler_cb_2'),'1') != 0 ){
							//日付が未来かどうかをチェック
							$lesson_date_unix = strtotime( $lesson_date[0] );
							$today_unix = strtotime(  date('Y-m-d') );
							//過去のものは表示しない
							if( $lesson_date_unix < $today_unix )continue;
						}
				    
			    		echo '<tr><td  data-id="'.get_the_ID().'" data-path="'.get_bloginfo('url').'">';
						echo  date("Y/m/d",strtotime($lesson_date[0]));
						echo '(';
						echo strftime( '%a', strtotime( $lesson_date[0] ) );
						echo ')';
			    		echo '</td>';
				    }
				    
					//練習場所を取得
				    $lesson_place = get_post_custom_values('lesson_schedule_field2');
				    if( $lesson_place  ){
			    		echo '<td>';
			            echo( $lesson_place[0] );
			    		echo '</td>';
				    }
				    
					//練習時間を取得
				    $lesson_time = get_post_custom_values('lesson_schedule_field3');
				    if( $lesson_time  ){
			    		echo '<td>';
			            echo( $lesson_time[0] );
			    		echo '</td>';
				    }
				    
				    //備考を取得
				    $remarks = get_post_custom_values('lesson_schedule_field4');
				    if( $remarks  ){
			    		echo '<td>';
			            echo( $remarks[0] );
			    		echo '</td>';
				    }else{
			    		echo '<td></td>';
				    }
			    ?>
			    <?php if(  is_user_logged_in() ) : ?>
			    	<!--出欠状況 -->
				    <td>
						<select name="syuketu<?php echo get_the_ID() ?>" size="1">
						<?php echo selectReply( get_the_ID(), $cu ); ?>
						</select>
						<input type="hidden" readonly="readonly" name="id<?php echo get_the_ID() ?>" value="<?php echo get_the_ID() ?>" />
					</td>
			    	<!--出欠状況 -->
				    <td>
                        <input type="text" name="comment<?php echo get_the_ID()?>" value="<?php 	echo get_post_meta(get_the_ID(), $cu->user_login."1", true) ?> " >
					</td>
				<?php endif; ?>
				<?php echo '</tr>'; ?>
			</div>
		<?php endwhile; // ループ終了 ?>


	</table>

    <?php if(  is_user_logged_in() ) : ?>
		<!-- 出欠ボタン -->
		<input type="submit" value="<?php _e('reply','lesson-scheduler'); ?>" />
	<?php endif; ?>

	<?php if(  is_user_logged_in() ) : ?>
		<h3><?php _e('others status','lesson-scheduler'); ?></h3>
		<table class="lesson_scheduler_table">
			<?php
				//全ユーザーの出欠状況を表示
				dispAllUser(); 
			 ?>
		</table>	
	<?php endif; ?>

</div>
</form>
<div id="dialog" title="lesson_scheduler">
    <p id="lesson_scheduler_dialog"></p>
</div>

<!-- 前の記事と後の記事へのリンクを表示 -->
<?php  if (  $wp_query->max_num_pages > 1 ) : ?>
	<br>
	<div id="nav-below" class="navigation">
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older lessons' ,'lesson-scheduler' ) ); ?></div>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer lessons <span class="meta-nav">&rarr;</span>' ,'lesson-scheduler' ) ); ?></div>
	</div><!-- #nav-below -->
	<br>
<?php endif; ?>

</div>
<?php

	wp_reset_query();

}


/* 練習日毎の出欠状況コンボボックス
-----------------------------------------------------------*/
function selectReply( $id, $cu ){
	$value = get_post_meta($id, $cu->user_login, true);

	$selected = ( $value == "" ) ? "selected" : "";
	echo "<option value='' ".$selected." ></option>";
	$selected = ( strcmp($value,"attend") == 0 ) ? "selected" : "";
	echo "<option value='attend' ".$selected." >".__('attend','lesson-scheduler')."</option>";
	$selected = ( strcmp($value,"absence") == 0 ) ? "selected" : "";
	echo "<option value='absence' ".$selected." >".__('absence','lesson-scheduler')."</option>";
	$selected = ( strcmp($value,"late") == 0 ) ? "selected" : "";
	echo "<option value='late' ".$selected." >".__('late','lesson-scheduler')."</option>";
	$selected = ( strcmp($value,"early") == 0 ) ? "selected" : "";
	echo "<option value='early' ".$selected." >".__('early','lesson-scheduler')."</option>";
	$selected = ( strcmp($value,"undecided") == 0 ) ? "selected" : "";
	echo "<option value='undecided' ".$selected." >".__('undecided','lesson-scheduler')."</option>";

}


/* 全ユーザー、練習日毎の状況表示
-----------------------------------------------------------*/
function dispAllUser(){

	//タイトル表示
	echo '<span style="font-size:0.9em">●:'.__('attend','lesson-scheduler').' ';
	echo '×:'.__('absence','lesson-scheduler').' ';
	echo '△:'.__('late','lesson-scheduler').' ';
	echo '□:'.__('early','lesson-scheduler').' ';
	echo '－:'.__('undecided','lesson-scheduler').'</span>';
	
	echo '<thead><tr>';
	echo '<th>名前</th>';
	
	//全カスタム投稿分ループ
	while ( have_posts() ){
	
		the_post();
	
		//練習日を取得
	    $lesson_date = get_post_custom_values('lesson_schedule_field1');
		if( $lesson_date  ){
			//過去の練習を出さない場合はチェックする
			if( strcmp(get_option('lesson_scheduler_cb_2'),'1') != 0 ){
				//日付が未来かどうかをチェック
				$lesson_date_unix = strtotime( $lesson_date[0] );
				$today_unix = strtotime(  date('Y-m-d') );
				//過去のものは表示しない
				if( $lesson_date_unix < $today_unix )continue;
			}
		}
		
		//練習日にリンクをはる
		echo '<th>';
		echo  date("m/d",strtotime($lesson_date[0]));
		echo '</th>';

	}
	
	echo '</tr></thead>';

	//全ユーザー情報の取得
	$users = get_users_of_blog();
	
	foreach ( $users as $users ){

		//ニックネーム出力
		echo '<td>';
		the_author_meta('nickname', $users->user_id);
		echo 'さん</td>';

		while ( have_posts() ){
			the_post();
		
			//練習日を取得
		    $lesson_date = get_post_custom_values('lesson_schedule_field1');
			if( $lesson_date  ){
				//過去の練習を出さない場合はチェックする
				if( get_option('lesson_scheduler_cb_2') != '1' ){
					/* 日付が未来かどうかをチェック */
					$lesson_date_unix = strtotime( $lesson_date[0] );
					$today_unix = strtotime(  date('Y-m-d') );
					/* 過去のものは表示しない */
					if( $lesson_date_unix < $today_unix )continue;
				}
			}

			//出欠状況の出力
			echo '<td>'; 
			$value = get_post_meta(get_the_ID(), $users->user_login, true);
			if( strcmp($value,"attend") == 0 ){
				echo '●';	//出席
			}elseif( strcmp($value,"absence") == 0 ){
				echo '×';	//欠席
			}elseif( strcmp($value,"late") == 0 ){
				echo '△';	//遅刻
			}elseif( strcmp($value,"early") == 0 ){
				echo '□';	//早退
			}elseif( strcmp($value,"undecided") == 0 ){
				echo '？';	//未定
			}else{
				echo '-----';	//未選択
			}
			echo '</td>'; 
		}
		echo '</tr>';

	}
	
	
	
}

/* タイトル自動設定
-----------------------------------------------------------*/
add_action('wp_insert_post', 'set_auto_title', 10, 2);
function set_auto_title($post_ID, $post){

	//タイトル自動化しない場合は抜ける
	if( get_option('lesson_scheduler_cb_1') != '1' )return 0;

 	global $wpdb;

 	/* ポストタイプがカスタムかどうかチェック */
	if( $post->post_type == 'lesson_schedules'  ){

		/* 練習日程を取得 */
		$lesson_date = get_post_custom_values('lesson_schedule_field1', $post_ID);
		if( $lesson_date ){
			$title_message = date('m/d',strtotime($lesson_date[0]));
		}
		else{
			$title_message = "NULL";
		}
		
		if( strcmp( $title_message , "NULL" ) != 0 ){

			/* 練習時間を取得 */
		    $lesson_time = get_post_custom_values('lesson_schedule_field3', $post_ID);
		    if( $lesson_time  ){
		    	$title_message = $title_message . '(';
				$title_message = $title_message . $lesson_time[0];
		    	$title_message = $title_message . ')';
		    }
			/* 練習場所を取得 */
		    $lesson_place = get_post_custom_values('lesson_schedule_field2', $post_ID);
		    if( $lesson_place  ){
				$title_message = $title_message . ' at ';
				$title_message = $title_message . $lesson_place[0];
		    }

			$where = array( 'ID' => $post_ID );
			$wpdb->update( $wpdb->posts, array( 'post_title' => $title_message ), $where );
			if ( $wp_error )
				return new WP_Error('db_update_error', __('Could not update post in the database'), $wpdb->last_error);
			
		}
		
	}
	
	return 0;

}


/* 日本語化
-----------------------------------------------------------*/
function lesson_scheduler_load_textdomain() {
	if ( function_exists('load_plugin_textdomain') ) {
		load_plugin_textdomain( 'lesson-scheduler', false, dirname( plugin_basename( __FILE__ )).'/languages' );
	}
}
add_action('init', 'lesson_scheduler_load_textdomain');

/* scriptの読み込み
-----------------------------------------------------------*/
function lesson_scheduler_add_script() {
    wp_register_script( 'jquery_1_8_3_js', 'http://code.jquery.com/jquery-1.8.3.js', false );
    wp_register_script( 'jquery_core_js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', false );
    wp_register_script( 'tablesorter_js', plugins_url('js/jquery.tablesorter.min.js', __FILE__), false );
    wp_register_script( 'lesson_scheduler_js', plugins_url('js/lesson_scheduler.js', __FILE__), false );
	wp_enqueue_script('jquery_1_8_3_js');
	wp_enqueue_script('jquery_core_js');
	wp_enqueue_script('tablesorter_js');
	wp_enqueue_script('lesson_scheduler_js');
}
add_action('wp_print_scripts','lesson_scheduler_add_script');

/* cssの読み込み
-----------------------------------------------------------*/
function lesson_scheduler_add_styles() {
    wp_register_style( 'lesson_scheduler_css', plugins_url('css/lesson_scheduler.css', __FILE__) );
	wp_enqueue_style('lesson_scheduler_css');
	echo '<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />'."\n";
}
add_action('wp_print_styles','lesson_scheduler_add_styles');

// 管理メニュー初期設定にフック
function lesson_scheduler_myplugin_admin_menu() {
	echo '<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />'."\n";
}
add_action('admin_head', 'lesson_scheduler_myplugin_admin_menu');

/* モバイルかどうかをチェックする 
-----------------------------------------------------------*/
function lesson_scheduler_chk_mobile(){

	//モバイルモードを利用しない場合はfalse
	if( get_option('lesson_scheduler_cb_3') != '1' )return false;

	$mobile = false;
	if (strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") || strpos($_SERVER['HTTP_USER_AGENT'],"Android") ){
		$mobile = true;
	}
	return $mobile;
}

add_filter( 'posts_orderby','my_posts_orderby', 10, 2 );
function my_posts_orderby( $orderby, $query ) {

    //管理ページは無視
    if( is_admin( ) ) return;
    
    //ポストタイプをチェック
    if(isset($query->query_vars['post_type']) & strcmp($query->query_vars['post_type'],'lesson_schedules')==0){
        $buf='ASC';
        //過去の練習日も表示する
        if( strcmp(get_option('lesson_scheduler_cb_2'),'1') == 0 ){
            $buf = 'DESC';
        }
        $orderby = "concat(right(meta_value,4),left(meta_value,2),mid(meta_value,4,2)) ".$buf;
        return $orderby;
    }
}


add_filter( 'posts_where_paged', 'my_post_where', 10, 2);
function my_post_where( $where, $query ) {
    
    //管理ページは無視
    if( is_admin( ) ) return $where;
    
    //ポストタイプをチェック
    if(isset($query->query_vars['post_type']) & strcmp($query->query_vars['post_type'],'lesson_schedules')==0){
        //過去の練習日を表示しない
        if( strcmp(get_option('lesson_scheduler_cb_2'),'1') != 0 ){
            //過去の練習を表示しない場合は、現在の日付以降を取得
            $today_unix =  date('Y-m-d');
            $where = $where.' AND (concat(right(meta_value,4),"-",left(meta_value,2),"-",mid(meta_value,4,2)) >="'.$today_unix.'")';
            return $where;
        }
    }

    return $where;
    
}

add_action('wp_ajax_get_lesson_detail', 'get_lesson_detail');
function get_lesson_detail(){

	$id = $_POST['data-id'];
	$retjson = array();
	

	$lesson_date = get_post_custom_values('lesson_schedule_field1',$id);
	$retjson['lesson_date'] =  date("m/d",strtotime($lesson_date[0]));
	
	/* lesson_timeをキーとして、練習時間を取得 */
    $lesson_time = get_post_custom_values('lesson_schedule_field3',$id);
    if( $lesson_time  ){
    	$retjson['lesson_time'] = $lesson_time[0];
    }
	/* lesson_placeをキーとして、練習場所を取得 */
    $lesson_place = get_post_custom_values('lesson_schedule_field2',$id);
    if( $lesson_place  ){
    	$retjson['lesson_place'] = $lesson_place[0];
    }
	/* lesson_descをキーとして、練習場所を取得 */
    $lesson_desc = get_post_custom_values('lesson_schedule_field4',$id);
    if( $lesson_desc  ){
    	$retjson['lesson_desc'] = $lesson_desc[0];
    }
    
    $retjson['user_status'] = dispScheduleDetail( $id );
    $retjson['id'] = $id;
    
    header('Content-Type: application/json charset=utf-8');
    echo json_encode($retjson);
    die();
}

function dispScheduleDetail( $id ){
	//全ユーザー情報の取得
	$users = get_users_of_blog();

	$user_status = array();		
    
	foreach ( $users as $users ){

		//ニックネーム出力
		$nicname =  get_the_author_meta('nickname', $users->user_id);

		//出欠状況の出力
		$value = get_post_meta($id, $users->user_login, true);
		if( strcmp($value,"attend") == 0 ){
			$status = '●';	//出席
		}elseif( strcmp($value,"absence") == 0 ){
			$status = '×';	//欠席
		}elseif( strcmp($value,"late") == 0 ){
			$status = '△';	//遅刻
		}elseif( strcmp($value,"early") == 0 ){
			$status = '□';	//早退
		}elseif( strcmp($value,"undecided") == 0 ){
			$status = '？';	//未定
		}else{
			$status = '-----';	//未選択
		}
		

		$comment = get_post_meta($id, $users->user_login."1", true);
        $val = array();
        $val['status'] = $status;
        $val['comment'] = $comment;
        $user_status[$nicname] = $val;
        
	}
    
	return $user_status;
	
}


?>
