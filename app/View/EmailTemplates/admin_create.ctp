<h3>Create Email Template</h3>
<?php
echo $this->Form->create('EmailTemplate',array('type' =>'file'));
echo $this->element('../EmailTemplates/elements');
echo '<div id="emailTemplateAdd" class="submit_button" style="display:none">
<a href="javascript:void(0)" id="emailTemFormPrev" class="btn btn-primary">Previous</a> &nbsp; &nbsp;'.
    $this->Form->end(array('label'=>'Save','class'=>'btn btn-primary', 'div'=>false)).'</div>';
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#EmailTemplateAdminCreateForm').validate({

        });
    });
</script>
