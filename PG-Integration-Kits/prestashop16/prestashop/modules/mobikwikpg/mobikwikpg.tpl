<div class='mobikwikPG' >
<form id='mobikwikPG' action="{$data.mobikwikpg_url}" method="post">
    <p class="payment_module"> 
    {foreach $data.info as $k=>$v}
        <input type="hidden" name="{$k}" value="{$v}" />
    {/foreach}  
     <a href='#' onclick='document.getElementById("mobikwikPG").submit();return false;' style= "padding-left: 20px;padding-bottom: 10px; padding-top: 10px">
     	<input type = "radio" value = "large">
     	<img title='Pay Now With MobikwikPG' src = "{$base_dir}modules/mobikwikpg/logo.png" style="padding-right:20px;">Pay By MobikwikPG
       </a>
       <noscript><input type="image" src="{$base_dir}modules/mobikwikpg/logo.png" ></noscript>
    </p> 
</form>
</div>
<div class="clear"></div>
