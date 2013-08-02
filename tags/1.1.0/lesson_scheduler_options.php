<?php

	// 管理メニューに追加するフック
	add_action('admin_menu', 'lesson_scheduler_add_menu');

	// 上のフックに対するaction関数
	function lesson_scheduler_add_menu() {
	    // 設定メニュー下にサブメニューを追加:
	    add_submenu_page('options-general.php','Lesson Scheduler', 'Lesson Scheduler', 8, __FILE__, 'lesson_scheduler_option_page' );
	}

	
/*  プラグイン用設定画面
-----------------------------------------------------------*/
	function lesson_scheduler_option_page() {
?>
<div class="wrap">
	<h2>lesson scheduler</h2>

	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	
	<!--練習場所の設定画面-->
	<table class="form-table" >
		<th scope="row"><?php _e('set lesson place','lesson-scheduler'); ?></th>
		<?php for( $i=1; $i<=10; $i++ ){ ?>
			<tr><td><input type="text" name="lesson_scheduler_place_<?php echo $i; ?>" value="<?php echo get_option('lesson_scheduler_place_'.$i); ?>" /></td></tr>
		<?php } ?>
	</span>
	</table>
	
	<!--練習時間の設定画面-->
	<table class="form-table">
		<th scope="row"><?php _e('set lesson time','lesson-scheduler'); ?></th>
		<?php for( $i=1; $i<=10; $i++ ){ ?>
			<tr><td><input type="text" name="lesson_scheduler_time_<?php echo $i; ?>" value="<?php echo get_option('lesson_scheduler_time_'.$i); ?>" /></td></tr>
		<?php } ?>
	</tr>
	</table>

	<table class="form-table" >
		<th scope="row"><?php _e('set option','lesson-scheduler') ?></th>
		<?php if( get_option('lesson_scheduler_cb_1') == '1' ) : ?>
			<tr><td><input type="checkbox" name="lesson_scheduler_cb_1" value="1" checked="checked"/><?php _e( 'auto title','lesson-scheduler') ?></td></tr>
		<?php else : ?>
			<tr><td><input type="checkbox" name="lesson_scheduler_cb_1" value="1" /><?php _e( 'auto title','lesson-scheduler') ?></td></tr>
		<?php endif; ?>

		<?php if( get_option('lesson_scheduler_cb_2') == '1' ) : ?>
			<tr><td><input type="checkbox" name="lesson_scheduler_cb_2" value="1" checked="checked"/><?php _e( 'print past schedules','lesson-scheduler') ?></td></tr>
		<?php else : ?>
			<tr><td><input type="checkbox" name="lesson_scheduler_cb_2" value="1" /><?php _e( 'print past schedules','lesson-scheduler') ?></td></tr>
		<?php endif; ?>

		<?php if( get_option('lesson_scheduler_cb_3') == '1' ) : ?>
			<tr><td><input type="checkbox" name="lesson_scheduler_cb_3" value="1" checked="checked"/><?php _e( 'used mobile phone mode','lesson-scheduler') ?></td></tr>
		<?php else : ?>
			<tr><td><input type="checkbox" name="lesson_scheduler_cb_3" value="1" /><?php _e( 'used mobile phone mode','lesson-scheduler') ?></td></tr>
		<?php endif; ?>

	</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="<?php get_alloption();?>"/>

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes','lesson-scheduler'); ?>" />
	</p>

	</form>
</div>
<?php }
//オプション用変数をすべてつなげる
function get_alloption()
{
	for( $i=1; $i<=10; $i++ ){
		$str = $str."lesson_scheduler_place_".$i.",";
	}
	
	for( $i=1; $i<=10; $i++ ){
		$str = $str."lesson_scheduler_time_".$i.",";
	}
	
	$str = $str."lesson_scheduler_cb_1,lesson_scheduler_cb_2,lesson_scheduler_cb_3";
	
	echo $str;
}


?>
