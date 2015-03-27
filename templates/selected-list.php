<script class="curator-selected-list-template" type="text/x-handlebars-template">

    {{#unless pages}}
        <div class="curator-selected-empty">
            <?php echo l::get('curator.selected.empty') ?>
        </div>
    {{/unless}}

    {{#if pages}}
        <div class="curator-pages cf">
            <div class="grid sortable ui-sortable">
                {{#each pages}}<!--
                --><div class="grid-item ui-sortable-handle [ js-curator-selected-item ]" data-uri="{{uri}}">
                        <figure class="file">
                            <a class="file-preview">
                                {{#if thumbs.large}}
                                    <img src="{{thumbs.large}}">
                                {{else}}
                                    <span>
                                        {{#equals ../template "text"}}
                                            <i class="icon fa fa-file-text-o"></i>
                                        {{else}}
                                            {{#equals ../template "picture"}}
                                                <i class="icon fa fa-file-image-o"></i>
                                            {{else}}
                                                <i class="icon fa fa-file-o"></i>
                                            {{/equals}}
                                        {{/equals}}
                                    </span>
                                {{/if}}
                            </a>
                            <figcaption class="file-info">
                                <a class="file-name cut">{{title}}</a>
                                <a class="file-meta marginalia cut">{{uri}}</a>
                            </figcaption>
                            <nav class="file-options cf">
                                <a class="btn btn-with-icon" href="{{links.edit}}">
                                    <i class="icon icon-left fa fa-pencil"></i>
                                    Edit
                                </a>
                                <a class="btn btn-with-icon [ js-curator-remove-button ]">
                                    <i class="icon icon-left fa fa-trash-o"></i>
                                    Remove
                                </a>
                            </nav>
                        </figure>
                    </div><!--
                -->{{/each}}
            </div>
        </div>
    {{/if}}
</script>
