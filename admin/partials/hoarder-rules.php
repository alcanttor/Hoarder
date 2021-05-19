<?php

//if("1" || "0")
//{
//    echo 'true';
//}
//else
//{
//    echo 'false';
//}

$dbhandler = new HOARDER_DBhandler;
$hoarderrequests = new HOARDER_request;

$condition = array('4'=>1,'5'=>0,'6'=>1);
$regularexpression = '4||5';
$string = $hoarderrequests->hoarder_final_condition_result($condition,$regularexpression);
//$result = eval("return (".$string.");");
if($string){
    echo 'true';
}
else
{
    echo 'false';
}

$path =  plugin_dir_url(__FILE__); 
$identifier = 'RULES';
$pagenum = filter_input(INPUT_GET, 'pagenum');
$pagenum = isset($pagenum) ? absint($pagenum) : 1;
$limit = 20; // number of rows in page
$offset = ( $pagenum - 1 ) * $limit;
$i = 1 + $offset;
$totalemails = $dbhandler->hoarder_count($identifier);
$emails =  $dbhandler->get_all_result($identifier,'*',1,'results',$offset,$limit,'id');
$num_of_pages = ceil( $totalemails/$limit);
$pagination = $dbhandler->hoarder_get_pagination($num_of_pages,$pagenum);
if(isset($_GET['selected']))
{
	$selected = filter_input(INPUT_GET, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $count_selected =  count($selected);
	foreach($selected as $tid)
	{
                $exist_tmpl = $hoarderrequests->pg_check_email_template_if_used_in_any_group($tid);
                if($exist_tmpl!=false)
                {
                    if($count_selected>1)
                    {
                        $msg = __('One or more email templates you are trying to delete are being used for notifications by a group. Please disassociate them before attempting to delete.','hoarder');
                    }
                    else
                    {
                        $msg = sprintf(__('The Email Template you are trying to delete is being used for notifications by group %s. Disassociate the template before deleting.','hoarder'),$exist_tmpl);
                    }  
                } 
                else
                {
                    $dbhandler->remove_row($identifier,'id',$tid,'%d');
                }
		
	}
	
        wp_redirect( esc_url_raw('admin.php?page=hoarder_email_templates') );exit;
}

?>

<div class="hoarderagic"> 
  
  <!-----Operationsbar Starts----->
  <form name="email_manager" id="email_manager" action="" method="get">
    <input type="hidden" name="page" value="hoarder_email_templates" />
    <input type="hidden" name="pagenum" value="<?php echo $pagenum;?>" />
    <div class="operationsbar">
      <div class="hoardertitle">
        <?php _e('Hoarder Rules','hoarder');?>
      </div>
      
    </div>
    <!--------Operationsbar Ends-----> 
    
    <!-------Contentarea Starts-----> 
    
    <!----Table Wrapper---->
    <?php if(isset($emails) && !empty($emails)):?>
    <div class="hoarderagic-table"> 
      
      <!----Sidebar---->
      
      <table class="pg-email-list">
        <tr>
          <th>&nbsp;</th>
            <th>&nbsp;</th>
          <th><?php _e('SR','hoarder');?></th>
          <th><?php _e('Name','hoarder');?></th>
          <th><?php _e('Subject','hoarder');?></th>
          <th><?php _e('Action','hoarder');?></th>
        </tr>
        <?php
	 	
			foreach($emails as $email)
			{
				?>
        <tr>
            <td><input type="checkbox" name="selected[]" class="pg-selected-email-tmpl" value="<?php echo $email->id; ?>" /></td>
          <td><i class="fa fa-envelope" aria-hidden="true"></i></td>
          <td><?php echo $i;?></td>
          <td><?php echo $email->tmpl_name;?></td>
          <td><?php echo $email->email_subject;?></td>
          <td><a href="admin.php?page=hoarder_add_email_template&id=<?php echo $email->id;?>">
<!--              <i class="fa fa-eye" aria-hidden="true"></i>-->
            <?php _e('Edit','hoarder');?>
            </a></td>
        </tr>
        <?php $i++; }?>
      </table>
    </div>
    
    <?php echo $pagination;?>
    <?php else:?>
	<div class="hoarder_message"><?php _e('There is no any rules created.','hoarder');?></div>
	<?php endif;?>
  </form>
</div>
