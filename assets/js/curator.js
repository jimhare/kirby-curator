
Curator = (function($, $field) {

    var self = this;

    /**
     * Basic configuration
     */
    this.name      = $field.data('name');
    this.config    = CuratorConfig[this.name];
    this.mode      = this.config.mode;
    this.intervals = new Array();
    this.pages     = CuratorItems[this.name];
    this.filtered  = this.pages;
    this.selected  = this.pages;

    /**
     * Cache relevant field elements
     */
    this.$field     = $field;
    this.$storage   = $field.find('.js-curator-storage');
    this.$results   = $field.find('.js-curator-results-zone');
    this.$selected  = $field.find('.js-curator-selected-zone');
    this.$filters   = $field.find('.js-curator-filter');
    this.$toggle    = $field.find('.js-curator-filter-toggle');

    /**
     * Cache filter input elements
     */
    this.$type     = $field.find('#form-field-' + this.name + '-type');
    this.$root     = $field.find('#form-field-' + this.name + '-root');
    this.$search   = $field.find('#form-field-' + this.name + '-search');
    this.$fromdate = $field.find('#form-field-' + this.name + '-date-from');
    this.$todate   = $field.find('#form-field-' + this.name + '-date-to');
    this.$tags     = $field.find('[name=' + this.name + '-tags]');
    this.$limit    = $field.find('#form-field-' + this.name + '-limit');

    /**
     * Prepare Handlebars templates
     */
    this.templates = {
        filteredList: Handlebars.compile(this.$field.find('.curator-filtered-list-template').html()),
        selectedList: Handlebars.compile(this.$field.find('.curator-selected-list-template').html())
    };

    /**************************************************************************\
    *                                 GENERAL                                  *
    ***************************************************************************/

    /**
     * Initialize curator field
     */
    this.init = function() {

        /*
            Set up custom Handlebars helpers
         */
        self.initHandlebarsHelpers();

        /*
            Split all pages tags strings into arrays
         */
        $.each(self.pages, function(index, page) {
            page.tags = page.tags.split(',');
        });

        /*
            Render initial filtered list
         */
        self.updateFiltered();

        /*
            Bind search and type fields event handler
         */
        self.$search.on('input', self.updateFiltered);
        self.$type.on('change', self.updateFiltered);
        self.$limit.on('input', self.updateFiltered);

        /*
            Set up periodic watchers for fields that may
            be changed via JS
         */
        self.watch(self.$fromdate, self.updateFiltered);
        self.watch(self.$todate, self.updateFiltered);
        self.watch(self.$tags, self.updateFiltered);
        self.watch(self.$root, self.updateFiltered);

        /**
         * Observe when the field element is destroyed (=the user leaves the
         * current view) and deactivate ourself accordingly.
         */
        self.$field.bind('destroyed', function() {
            self.deactivate();
        });

        /*
            Special initialization steps for the "curation" mode
         */
        if(self.mode == 'curation') {
            self.initCurationMode();
        }

    };

    /**
     * Deactivate
     */
    this.deactivate = function() {
        /*
            Clear all watcher timers
         */
        self.intervals.forEach(function(id, index, array) {
            clearInterval(id);
        });
    };

    /**
     * Load custom Handlebars helpers
     */
    this.initHandlebarsHelpers = function() {
        /*
            Custom equals helper
         */
        Handlebars.registerHelper('equals', function(left, right, options) {
            if(arguments.length < 3) {
                throw new Error("Handlebars helper \"equals\" needs 2 parameters");
            }
            if(left != right) {
                return options.inverse(this);
            } else {
                return options.fn(this);
            }
        });
    };

    /**
     * Update filtered pages list
     *
     * Stores the filter if the plugin is in "aggregation" mode
     * and updates the filtered results list.
     */
    this.updateFiltered = function(e) {
        if(self.mode == 'aggregation') {
            self.updateAggregationStorage();
        }
        self.updateFilteredList();
    };

    /**
     * Refilter results and rerender results list
     */
    this.updateFilteredList = function(e) {
        self.filtered = self.filterPages();
        self.renderFilteredList();
    };

    /**
     * Render filtered list
     */
    this.renderFilteredList = function() {
        self.$results.html(self.templates.filteredList({
            pages:  self.filtered,
            config: self.config
        }));
    };

    /**
     * Filter pages based on user filters
     */
    this.filterPages = function() {
        var results = new Array(),
            root,
            type,
            query,
            fromdate,
            todate,
            tags,
            limit;

        /*
            Prepare type filter
         */
        type = (self.$type.val() == 'all')
            ? false
            : self.$type.val();

        /*
            Prepare root page filter
         */
        root = (self.$root.val() == '')
            ? false
            : self.$root.val();

        /*
            Prepare search query filter
         */
        query = (self.$search.val() == '')
            ? false
            : new RegExp(self.$search.val(), 'i'),

        /*
            Prepare date filter
         */
        fromdate = (self.$fromdate.val() == '')
            ? false
            : self.$fromdate.val();
        todate = (self.$todate.val() == '')
            ? false
            : self.$todate.val();

        /*
            Prepare tags filter
         */
        tags = (self.$tags.val() == '')
            ? false
            : self.$tags.val().split(',');

        /*
            Prepare limit filter
         */
        limit = (self.$limit.val() == '')
            ? false
            : parseInt(self.$limit.val());

        /*
            Filter pages
         */
        var results = new Array();
        $.each(self.pages, function(index, page) {

            if(   (!root  || self.matchRootPage(page, root))
               && (!type  || self.matchType(page, type))
               && (!query || self.matchQuery(page, query))
              && ((!fromdate && !todate) || self.matchDate(page, fromdate, todate))
               && (!tags  || self.matchTags(page, tags))) {
                results.push(page);
            }

        });

        /*
            Limit pages
         */
        if((limit !== false) && !isNaN(limit)) {
            results = results.slice(0, limit);
        }

        return results;
    };

    /**
     * Check if the page type matches
     */
    this.matchType = function(page, type) {
        return (page.template == type);
    };

    /**
     * Check if the pages path matches the root path
     */
    this.matchRootPage = function(page, root) {
        return page.uri.indexOf(root) === 0;
    }

    /**
     * Check if the query matches
     */
    this.matchQuery = function(page, regexp) {
        return (page.title.search(regexp) !== -1)
            || (page.texts.search(regexp) !== -1);
    };

    /**
     * Check if the from and to dates matche
     */
    this.matchDate = function(page, fromdate, todate) {
        return ((page.date >= fromdate) && (page.date <= todate))
            || (!fromdate && (page.date <= todate))
            || (!todate && (page.date >= fromdate));
    };

    /**
     * Check if the tags matche
     */
    this.matchTags = function(page, tags) {
        return self.intersect(page.tags, tags).length > 0;
    };

    /**************************************************************************\
    *                               AGGREGATION                                *
    ***************************************************************************/

    /**
     * Update "aggregation" mode storage
     */
    this.updateAggregationStorage = function() {
        var root     = self.$root.val().toLowerCase(),
            type     = self.$type.val(),
            query    = self.$search.val().toLowerCase(),
            fromdate = self.$fromdate.val(),
            todate   = self.$todate.val(),
            tags     = self.$tags.val().toLowerCase(),
            limit    = self.$limit.val(),
            data;

        data = {
            root:     root,
            type:     type,
            query:    query,
            fromdate: fromdate,
            todate:   todate,
            tags:     tags,
            limit:    limit
        };

        self.$storage.val(encodeURIComponent(JSON.stringify(data)));
    };

    /**************************************************************************\
    *                                 CURATION                                 *
    ***************************************************************************/

    /**
     * Initialize curation mode
     */
    this.initCurationMode = function() {

        /*
            Hide filters and bind toggle button event
         */
        self.$filters.hide();
        self.$toggle.on('click', self.toggleFilters);

        /*
            Render selected pages list
         */
        self.initSelected();
        self.renderSelectedList();

        /*
            Bind "Add" and "Remove" event handlers
         */
        self.$field.on('click', '.js-curator-add-button', self.handleAddButton);
        self.$field.on('click', '.js-curator-remove-button', self.handleRemoveButton);
    };

    /**
     * Show/hide "Add a Page"/"Hide Filters" button
     */
    this.toggleFilters = function(e) {
        e.preventDefault();

        if(self.$toggle.data('visible') == true) {
            self.$filters.hide();
            self.$toggle
                .data('visible', false)
                .find('i')
                    .removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                .parent().find('span')
                    .text(self.config.texts.addapage);

        } else {
            self.$filters.show();
            self.$toggle
                .data('visible', true)
                .find('i')
                    .removeClass('fa-plus-circle')
                    .addClass('fa-minus-circle')
                .parent().find('span')
                    .text(self.config.texts.hidefilters);
        }
    };

    /**
     * Init selected items with previously selected items
     */
    this.initSelected = function() {
        uris = JSON.parse(decodeURIComponent(self.$storage.val()));
        self.reorderSelected(uris);
    };

    /**
     * Reorder selected items based on a list of uris
     */
    this.reorderSelected = function(uris) {
        var index;

        self.selected = new Array();
        $.each(uris, function(i, uri) {
            index = self.findPageByUri(uri);
            if(index !== false) {
                self.selected.push(self.pages[index]);
            }
        });

        self.updateCurationStorage();
    };

    this.handleAddButton = function(e) {
        var $item = $(this).closest('.js-curator-filtered-item'),
            uri   = $item.data('uri'),
            index = self.findPageByUri(uri);

        /*
            Decide if the item needs to be added or removed
         */
        if(index !== false)
        {
            if(self.pages[index].selected) {
                self.removeSelected(index, uri);
            }
            else {
                self.addSelected(index, uri);
            }
        }

        e.preventDefault();
    };

    this.handleRemoveButton = function(e) {
        var $item  = $(this).closest('.js-curator-selected-item'),
            uri    = $item.data('uri'),
            index  = self.findPageByUri(uri);

        self.removeSelected(index, uri);
        e.preventDefault();
    };

    /**
     * Add a selected item
     */
    this.addSelected = function(index, uri) {
        /*
            Add item
         */
        if(index !== false) {
            self.pages[index].selected = true;
            self.selected.push(self.pages[index]);
        }

        /*
            Update storage and render lists
         */
        self.updateCurationStorage();
        self.renderFilteredList();
        self.renderSelectedList();
    };

    /**
     * Remove a selected item
     */
    this.removeSelected = function(index, uri) {
        /*
            Find item to remove
         */
        var remove = false;

        $.each(self.selected, function(i, page) {
            if(page.uri == uri) {
                remove = i;
            }
        });

        /*
            Remove item
         */
        if(index !== false) {
            self.pages[index].selected = false;
        }
        if(remove !== false) {
            self.selected.splice(remove, 1);
        }

        /*
            Update storage and render lists
         */
        self.updateCurationStorage();
        self.renderFilteredList();
        self.renderSelectedList();
    }

    /**
     * Render selected items list
     */
    this.renderSelectedList = function() {

        /*
            Destroy old sortable interaction
         */
        self.$selected.find('.sortable').sortable('destroy');

        /*
            Render template
         */
        self.$selected.html(self.templates.selectedList({
            pages:  self.selected,
            config: self.config
        }));

        /*
            Set up new sortable interaction
         */
        self.$selected.find('.sortable').sortable({
            update: function() {
                self.reorderSelected($(this).sortable('toArray', {attribute: 'data-uri'}));
            }
        }).disableSelection();
    };

    /**
     * Update the curation storage with the current state
     */
    this.updateCurationStorage = function() {
        var data = new Array();

        $.each(self.selected, function(index, page) {
            data.push(page.uri);
        });

        self.$storage.val(encodeURIComponent(JSON.stringify(data)));
    };

    /**************************************************************************\
    *                                 HELPERS                                  *
    ***************************************************************************/

    /**
     * Find a page from self.pages by its uri and return its index
     */
    this.findPageByUri = function(uri) {
        var found = false;

        $.each(self.pages, function(index, page) {
            if(page.uri == uri) {
                found = index;
            }
        });

        return found;
    };

    /**
     * Watch an input field for changes
     */
    this.watch = function($input, callback) {
        var old = $input.val(),
            id;

        id = setInterval(function() {
            if(old != $input.val()) {
                old = $input.val();
                callback();
            }
        }, 500);

        /*
            Push interval ID so we can use clearInterval() later
         */
        self.intervals.push(id);
    };

    /**
     * Calculate the intersection of two arrays
     */
    this.intersect = function(a, b) {
        return $.grep(a, function(i) {
            return $.inArray(i, b) > -1;
        });
    };

    return this.init();

});

(function($) {

    /**
     * Set up special "destroyed" event.
     *
     * @since 1.0.0
     */
    $.event.special.destroyed = {
        remove: function(event) {
            if(event.handler) {
                event.handler.apply(this, arguments);
            }
        }
    };

    /**
     * Tell the Panel to run our initialization.
     *
     * This callback will fire for every Curator
     * Field on the current panel page.
     *
     * @see https://github.com/getkirby/panel/issues/228#issuecomment-58379016
     * @since 1.0.0
     */
    $.fn.curator = function() {
            return new Curator($, this);
    };

})(jQuery);
