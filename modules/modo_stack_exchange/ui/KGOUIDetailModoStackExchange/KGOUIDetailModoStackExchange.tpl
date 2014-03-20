{*
    This is a smarty template. This text will not be rendered because this is in a smarty comment
        See http://www.smarty.net for syntax.


    This template will define the structure of the KGOUIDetailModoStackExchange object
    It wraps the fields 'thumbnail', 'title', and 'author' in a 'detail header' div
    Then it declares the regions 'actions' and 'content'
*}

{object_wrapper}
    <div class="kgo_inset kgoui_detail_header">
        {field_is_set_wrapper field="thumbnail" class="kgoui_detail_thumbnail"}

        {field_is_set_wrapper field="title" tag="h1" class="kgoui_detail_title"}

        {field_is_set_wrapper field="author" tag="h4" class="kgoui_detail_author"}
    </div>

    {include region="actions"}

    {include region="content"}

{/object_wrapper}
