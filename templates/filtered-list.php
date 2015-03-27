<script class="curator-filtered-list-template" type="text/x-handlebars-template">

    <div class="input input-with-items">
        {{#unless pages}}
            <div class="item curator-filtered-item-empty">
                No matching pages found.
            </div>
        {{/unless}}

        {{#each pages}}
            <div class="item item-with-image [ curator-filtered-item js-curator-filtered-item {{#if selected}}curator-filtered-item-selected{{/if}} ]" data-uri="{{uri}}">
                <div class="item-content">
                    {{#if thumbs.small}}
                        <figure class="item-image">
                                <a class="item-image-container" href="{{links.edit}}">
                                    <img src="{{thumbs.small}}" width="48" height="48" />
                                </a>
                        </figure>
                    {{else}}
                        <figure class="item-image  item-nothumb">
                            <a class="item-image-container" href="{{links.edit}}">
                                {{#equals ../template "text"}}
                                    <i class="icon fa fa-file-text-o"></i>
                                {{else}}
                                    {{#equals ../template "picture"}}
                                        <i class="icon fa fa-file-image-o"></i>
                                    {{else}}
                                        <i class="icon fa fa-file-o"></i>
                                    {{/equals}}
                                {{/equals}}
                            </a>
                        </figure>
                    {{/if}}
                    <div class="item-info">
                        <strong class="item-title">
                            <a href="{{links.edit}}">
                                {{title}}
                            </a>
                        </strong>
                        <small class="item-meta marginalia">
                            {{uri}}
                        </small>
                    </div>
                </div>

                <nav class="item-options">
                    <ul class="nav nav-bar">
                        <li>
                            <a class="btn btn-with-icon" href="{{links.preview}}" title="<?= l('pages.show.preview') ?>" target="_blank">
                                <i class="icon icon-left fa fa-play-circle-o"></i>
                            </a>
                        </li>
                        {{#if ../config.curationMode}}
                            <li>
                                <a class="btn btn-with-icon [ curator-button js-curator-add-button ]" href="#" title="<?= l('pages.show.subpages.add') ?>">
                                    {{#if selected}}
                                        <i class="icon icon-left fa fa-check-circle"></i>
                                    {{else}}
                                        <i class="icon icon-left fa fa-circle-o"></i>
                                    {{/if}}
                                </a>
                            </li>
                        {{/if}}
                    </ul>
                </nav>
            </div>
        {{/each}}
    </div>

</script>
