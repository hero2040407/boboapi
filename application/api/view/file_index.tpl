<style>
a.hover{ldelim}
color:red;
{rdelim}
</style>

{if $pageinfo->totalItemCount }
<div class="paginationControl">
<!-- Previous page link -->
{ if $pageinfo->previous }
  <a href="{$urlhelp->url($pageinfo->previous)}">
    &lt; Previous
  </a> &nbsp;
{else}
  <span class="disabled"></span> &nbsp;
{/if}


{if (1 != $pageinfo->current)}
<a  href="{$urlhelp->url(1)}">1</a>  &nbsp;
{else}
<a class="hover" >1</a> &nbsp;
  {/if}


{if $pageinfo->firstPageInRange > 2}
<a >……</a>
{/if}


{foreach from=$pageinfo->pagesInRange item=page }
 {if $page != 1 and $page != $pageinfo->pageCount}

  {if ($page != $pageinfo->current)}
    <a href="{$urlhelp->url($page)}">{$page}</a> &nbsp;
   {else}
    <a class='hover'>{$page}</a> &nbsp;
  {/if}
  
 {/if} 
  
{/foreach}


{if $pageinfo->pageCount - $pageinfo->lastPageInRange > 1}
<a>……</a>
{/if}


{if ($pageinfo->pageCount != $pageinfo->current)}
<a  href="{$urlhelp->url($pageinfo->pageCount)}">{$pageinfo->pageCount}</a> 
{else}
<a class="hover" ">{$pageinfo->pageCount}</a> 

  {/if}


{if $pageinfo->next}
  <a href="{$urlhelp->url($pageinfo->next)}">
    Next &gt;
  </a>
{else}
  <span class="disabled"></span>
{/if}
</div>

{/if}