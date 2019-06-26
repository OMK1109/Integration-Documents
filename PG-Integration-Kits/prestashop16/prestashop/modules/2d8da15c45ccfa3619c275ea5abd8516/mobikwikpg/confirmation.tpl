

{if $status == 'ok'}
    <p>{l s='Your order on' mod='mbk'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='mbk'}
        <br /><br /><span class="bold">{l s='Your order will be shipped as soon as possible.' mod='mbk'}</span>
        <br /><br />{l s='For any questions or for further information, please contact our' mod='mbk'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='mbk'}</a>.
    </p>
{else}
    {if $status == 'pending'}
        <p>{l s='Your order on' mod='mbk'} <span class="bold">{$shop_name}</span> {l s='is pending.' mod='mbk'}
            <br /><br /><span class="bold">{l s='Your order will be shipped as soon as we receive your bankwire.' mod='mbk'}</span>
            <br /><br />{l s='For any questions or for further information, please contact our' mod='mbk'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='mbk'}</a>.
        </p>
    {else}
        <p class="warning">
            {l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='mbk'} 
            <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='mbk'}</a>.
        </p>
    {/if}
{/if}
