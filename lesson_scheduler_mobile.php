<?php

function disp_lesson_scheduler_mobile() {


	//クエリーによりカスタム投稿読み込み
	
	//1度に10件表示
	$lesson_schedule_per_page = 10;
	
	//複数ページの場合に、選択されたページを取得
	$paged = get_query_var('paged');
	query_posts( "posts_per_page=$lesson_schedule_per_page&paged=$paged&post_type=lesson_schedules&orderby=meta_value&meta_key=lesson_schedule_field1" );
	
	global $wp_query;
/*	
	//記事があれば投稿データをロード（表示はしない） 
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
<dl>
	<!-- タイトルの表示 -->
	<h3><?php _e('schedule','lesson-scheduler') ?></h3>
	<hr>
	<!-- 練習ループ -->
	<?php while ( have_posts() ){
	
	 	the_post();

		$cu = wp_get_current_user();

		//送信ボタンが押されたかつ、その時のIDと同一ならば登録
		if ($_POST['syuketu'.get_the_ID()] != '' && strcmp( $_POST['id'.get_the_ID()], get_the_ID()) == 0 ) {
			delete_post_meta( get_the_ID(),  $cu->user_login ); 
			update_post_meta( get_the_ID(),  $cu->user_login, $_POST['syuketu'.get_the_ID()]);
            delete_post_meta( get_the_ID(),  $cu->user_login."1" ); 
            update_post_meta( get_the_ID(),  $cu->user_login."1", $_POST['comment'.get_the_ID()]);
		}
        

        echo "<div class='lesson_scheduler_mobile'  data-id='".get_the_ID()."' data-path='".get_bloginfo('url')."'>";
		$post = get_post($post_id);
		/* lesson_dateをキーとして、練習日を取得 */
	    $lesson_date = get_post_custom_values('lesson_schedule_field1',$post->ID);
	    if( $lesson_date  ){
			echo  date("y/m/d",strtotime($lesson_date[0]));
			echo '(';
			echo strftime( '%a', strtotime( $lesson_date[0] ) );
			echo ')';
	    	echo( '<br>');
	    }

		/* lesson_timeをキーとして、練習時間を取得 */
	    $lesson_time = get_post_custom_values('lesson_schedule_field2',$post->ID);
	    if( $lesson_time  ){
            echo( $lesson_time[0] );
	    	echo( '<br>');
	    }
	    
		/* lesson_placeをキーとして、練習場所を取得 */
	    $lesson_place = get_post_custom_values('lesson_schedule_field3',$post->ID);
	    if( $lesson_place  ){
            echo( $lesson_place[0] );
	    	echo( '<br>');
	    }
	    /* remarksをキーとして、備考を取得 */
	    $remarks = get_post_custom_values('lesson_schedule_field4',$post->ID);
	    if( $remarks  ){
            echo( $remarks[0] );
	    	echo( '<br>');
		}
		
        echo '</div><div class="lesson_scheduler_mobile_input">';
        
	    ?><?php if(  is_user_logged_in() ) : ?>
	    	<!--出欠状況 -->
            <select name="syuketu<?php echo get_the_ID() ?>" size="1">
            <?php echo selectReply( get_the_ID(), $cu ); ?>
            </select>

            <input type="hidden" readonly="readonly" name="id<?php echo get_the_ID() ?>" value="<?php echo get_the_ID() ?>" />
            <input type="text" name="comment<?php echo get_the_ID()?>" value="<?php 	echo get_post_meta(get_the_ID(), $cu->user_login."1", true) ?> " ><BR>
		<?php endif; ?><?php

		//出席人数表示
		dispAttendUser();

	    echo('<hr></div>');

	} ?>

    <?php if(  is_user_logged_in() ) : ?>
		<!-- 出欠ボタン -->
		<BR><input type="submit" value=<?php _e('reply','lesson-scheduler'); ?> /><BR>
	<?php endif; ?>

<div id="dialog" title="lesson_scheduler">
    <p id="lesson_scheduler_dialog"></p>
</div>
        
<!-- 前の記事と後の記事へのリンクを表示 -->
<?php

if ( $wp_query->max_num_pages > 1 ) : ?>
	<BR>
	<div id="nav-below" class="navigation">
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older lessons' ,'lesson-scheduler' ) ); ?></div>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer lessons <span class="meta-nav">&rarr;</span>' ,'lesson-scheduler' ) ); ?></div>
	</div><!-- #nav-below -->
	<BR>
<?php endif; ?>

</dl>

<?php

	wp_reset_query();

}

/* 出席者表示
-----------------------------------------------------------*/
function dispAttendUser(){

	$attend = 0;
	$absence = 0;
	$late = 0;
	$early = 0;
	$undecided = 0;
	
	//全ユーザー情報の取得
	$users = get_users_of_blog();
	
	foreach ( $users as $users ){

		//出欠状況の出力
		$value = get_post_meta(get_the_ID(), $users->user_login, true);
		if( strcmp($value,"attend") == 0 ){
			$attend++;	//出席
		}elseif( strcmp($value,"absence") == 0 ){
			$absence++;	//欠席
		}elseif( strcmp($value,"late") == 0 ){
			$late++;	//遅刻
		}elseif( strcmp($value,"early") == 0 ){
			$early++;	//早退
		}elseif( strcmp($value,"undecided") == 0 ){
			$undecided++;	//未定
		}
	}

	echo "●:".$attend." ×:".$absence." △:".$late." □:".$early." ？:".$undecided;	
}

add_filter( 'posts_orderby','my_posts_orderby_mobile', 10, 2 );
function my_posts_orderby_mobile( $orderby, $query ) {

    //管理ページは無視
    if( is_admin( ) ) return;
    
    //ポストタイプをチェック
    if(isset($query->query_vars['post_type']) & strcmp($query->query_vars['post_type'],'lesson_schedules')==0){
        $buf='DESC';
        //過去の練習日も表示する
        if( strcmp(get_option('lesson_scheduler_cb_2'),'1') == 0 ){
            $buf = 'ASC';
        }
        $orderby = "concat(right(meta_value,4),left(meta_value,2),mid(meta_value,4,2)) ".$buf;
        return $orderby;
    }
}


add_filter( 'posts_where_paged', 'my_post_where_mobile', 10, 2);
function my_post_where_mobile( $where, $query ) {
    
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


?>
