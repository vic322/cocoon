<?php //ビジュアルエディターの吹き出し挿入ドロップダウン

add_action('admin_init', 'add_speech_bolloons_dropdown');
add_action('admin_head', 'generate_speech_balloons_is');

if ( !function_exists( 'add_speech_bolloons_dropdown' ) ):
function add_speech_bolloons_dropdown(){
  if( current_user_can('edit_posts') &&  current_user_can('edit_pages') )  {
    add_filter( 'mce_external_plugins',  'add_speech_bolloons_to_mce_external_plugins' );
    add_filter( 'mce_buttons_2',  'register_speech_bolloons' );
  }
}
endif;

//ボタン用スクリプトの登録
if ( !function_exists( 'add_speech_bolloons_to_mce_external_plugins' ) ):
function add_speech_bolloons_to_mce_external_plugins( $plugin_array ){
  $path=get_template_directory_uri() . '/js/speech-balloons.js';
  $plugin_array['speech_bolloons'] = $path;
  //_v($plugin_array);
  return $plugin_array;
}
endif;

//吹き出しドロップダウンをTinyMCEに登録
if ( !function_exists( 'register_speech_bolloons' ) ):
function register_speech_bolloons( $buttons ){
  array_push( $buttons, 'separator', 'speech_bolloons' );
  return $buttons;
}
endif;

//吹き出しの値渡し用のJavaScriptを生成
if ( !function_exists( 'generate_speech_balloons_is' ) ):
function generate_speech_balloons_is($value){
  $records = get_speech_balloons(null, 'title');

  echo '<script type="text/javascript">
  var speechBalloonsTitle = "'.__( '吹き出し', THEME_NAME ).'";
  var speechBalloonsEmptyText = "'.__( '内容を入力してください。', THEME_NAME ).'";
  var speechBalloons = new Array();';

  $count = 0;

  foreach($records as $record){
    //非表示の場合は跳ばす
    if (!$record->visible) {
      continue;
    }
    ob_start();
    generate_speech_balloon_tag($record, 'VOICE');
    $speech_balloon_tag = ob_get_clean();
    $speech_balloon_tag_split = explode('VOICE', $speech_balloon_tag);
    //JavaScriptで改行エラーになるため取り除く
    $sb_tag_before = minify_html($speech_balloon_tag_split[0]);
    $sb_tag_after  = minify_html($speech_balloon_tag_split[1]);
    ?>

    var count = <?php echo $count; ?>;
    speechBalloons[count] = new Array();
    speechBalloons[count].title  = '<?php echo $record->title; ?>';
    speechBalloons[count].id     = '<?php echo $record->id; ?>';
    speechBalloons[count].before = '<?php echo $sb_tag_before; ?>';
    speechBalloons[count].after  = '<?php echo $sb_tag_after; ?>';

    <?php
    $count++;
  }
  echo '</script>';
}
endif;

