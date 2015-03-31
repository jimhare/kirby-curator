<?php

class CuratorField extends BaseField {

    /**
     * Language directory
     */
    const LANG_DIR = 'languages';

    /**
     * Define frontend assets
     *
     * @var array
     */
    public static $assets = array(
        'css' => array(
            'curator.css',
        ),
        'js' => array(
            'curator.js',
        ),
    );

    /**
     * Select mode (aggregation/curation)
     *
     * @var string
     */
    public $mode = 'aggregation';

    /**
     * Included pages (all/children)
     *
     * @var string
     */
    public $pages = 'all';

    /**
     * Sort order
     *
     * @var string
     */
    public $sort = 'default';

    /**
     * Flip sort order
     *
     * @var string
     */
    public $flip = false;

    /**
     * Valid option values
     *
     * @var array
     */
    protected $validValues = array(
        'mode'  => array(
            'aggregation',
            'curation',
        ),
        'pages' => array(
            'all',
            'children',
        ),
    );

    protected $figuresCache = array();

    /**************************************************************************\
    *                          GENERAL FIELD METHODS                           *
    \**************************************************************************/

     /**
     * Field setup
     *
     * (1) Load language files
     *
     * @return \CuratorField
     */
    public function __construct()
    {
        /*
            (1) Load language files
         */
        $baseDir = __DIR__ . DS . self::LANG_DIR . DS;
        $lang    = panel()->language();
        if(file_exists($baseDir . $lang . '.php'))
        {
            require $baseDir . $lang . '.php';
        }
        else
        {
            require $baseDir . 'en.php';
        }
    }

    /**
     * Magic setter
     *
     * Set a fields property and apply default value if required.
     *
     * @param string $option
     * @param mixed  $value
     */
    public function __set($option, $value)
    {
        /* Set given value */
        $this->$option = $value;

        /* Check if value is valid */
        switch($option)
        {
            case 'mode':
                if(!in_array($value, $this->validValues['mode']))
                    $this->mode ='aggregation';
                break;

            case 'pages':
                if(!in_array($value, $this->validValues['pages']))
                    $this->mode ='all';
                break;

            case 'sort':
                if(!is_string($value) or empty($value))
                    $this->sort = 'default';
                break;
            case 'flip':
                if(!is_bool($value))
                    $this->flip = false;
                break;
        }
    }

    /**
     * Convert result to YAML
     *
     * @return string
     */
    public function result()
    {
        $result  = parent::result();

        if($this->mode == 'aggregation')
        {
            $raw     = json_decode(rawurldecode($result), true);
            $data[0] = $raw;
            return yaml::encode($data);
        }
        if($this->mode == 'curation')
        {
            $data = json_decode(rawurldecode($result), true);
            return yaml::encode($data);
        }
        return null;
    }

    /**
     * Prepare previously saved value data
     *
     * @return array
     */
    public function value()
    {
        if($this->mode == 'aggregation')
        {
            $data = yaml::decode($this->value);
            $data = (is_array($data) and isset($data[0])) ? $data[0] : array();
            foreach(array('query', 'fromdate', 'todate', 'tags', 'root', 'type', 'limit') as $key)
            {
                if(!isset($data[$key]))
                {
                    $data[$key] = '';
                }
            }
            $this->value = $data;
        }
        if($this->mode == 'curation')
        {
            $data = yaml::decode($this->value);
            if(!is_array($data))
            {
                $data = array();
            }
            $this->value = $data;
        }

        return $this->value;
    }

    /**************************************************************************\
    *                            PANEL FIELD MARKUP                            *
    \**************************************************************************/

    /**
     * Generate field content markup
     *
     * @return string
     */
    public function content()
    {
        $this->value();

        $wrapper = new Brick('div');
        $wrapper->addClass('curator');
        $wrapper->data(array(
            'field' => 'curator',
            'name'  => $this->name(),
            'mode'  => $this->mode,
        ));
        $wrapper->html(tpl::load(__DIR__ . DS . 'template.php', array('field' => $this)));
        return $wrapper;
    }

    /**
     * Remove parents default label
     *
     * @return null;
     */
    public function label()
    {
        return null;
    }

    /**
     * Generate type input markup
     *
     * @return string
     */
    public function typeInput()
    {
        $input = new Brick('select');
        $input->addClass('selectbox');
        $input->attr(array(
            'name'         => $this->name() . '-type',
            'id'           => $this->id() . '-type',
        ));
        $input->append($this->typeInputOption('all', l::get('curator.filter.type.all')));
        $input->append($this->typeInputOption('text', l::get('curator.filter.type.text')));
        $input->append($this->typeInputOption('picture', l::get('curator.filter.type.picture')));

        $inner = new Brick('div');
        $inner->addClass('selectbox-wrapper');
        $inner->append($input);

        $wrapper = new Brick('div');
        $wrapper->addClass('input input-with-selectbox');
        $wrapper->attr('data-focus', 'true');
        $wrapper->append($inner);

        return $wrapper;
    }

    /**
     * Generate markup for a single type input <option> tag
     *
     * @param  string $value
     * @param  string $text
     * @return string
     */
    protected function typeInputOption($value, $text)
    {
        return new Brick('option', $text, array(
            'value'    => $value,
            'selected' => ($this->mode == 'aggregation')  and $this->value['type'] == $value ? 'selected' : '',
        ));
    }

    /**
     * Generate root page input
     *
     * @return string
     */
    public function rootPageInput()
    {
        $input = new Brick('input', null);
        $input->addClass('input');
        $input->attr(array(
            'type'         => 'text',
            'value'        => ($this->mode == 'aggregation') ? $this->value['root'] : '',
            'name'         => $this->name() . '-root',
            'id'           => $this->id() . '-root',
            'autocomplete' => $this->autocomplete() === false ? 'off' : 'on',
            'placeholder'  => l::get('fields.page.placeholder', 'path/to/page'),
        ));
        $input->data(array(
            'field' => 'autocomplete',
            'url'   => panel()->urls()->api() . '/autocomplete/uris'
        ));
        return $input;
    }

    /**
     * Generate search input markup
     *
     * @return string
     */
    public function searchInput()
    {
        $input = new Brick('input', null);
        $input->addClass('input');
        $input->attr(array(
            'type'         => 'text',
            'name'         => $this->name() . '-search',
            'autocomplete' => 'off',
            'id'           => $this->id() . '-search',
            'placeholder'  => l::get('curator.filter.keyword.placeholder'),
            'value'        => ($this->mode == 'aggregation') ? $this->value['query'] : '',
        ));
        return $input;
    }

    /**
     * Generate tags input markup
     *
     * @return string
     */
    public function tagsInput()
    {
        $input = new Brick('input', null);
        $input->addClass('input input-with-tags');
        $input->attr(array(
            'type'         => 'tags',
            'name'         => $this->name() . '-tags',
            'autocomplete' => 'on',
            'id'           => $this->id() . '-tags',
            'value'        => ($this->mode == 'aggregation') ? $this->value['tags'] : '',
        ));
        $input->data(array(
            'field'     => 'tags',
            'lowercase' => true,
            'separator' => ',',
            'url'       => panel()->urls()->api() . '/autocomplete/field?' . http_build_query(array(
                'uri'       => $this->page()->id(),
                'index'     => 'all',
                'field'     => 'tags',
                'separator' => ','
            )),
        ));

        return $input;
    }

    /**
     * Generate date input markup
     *
     * @return string
     */
    public function dateInput($type)
    {
        /*
            Create visual input field
         */
        $input = new Brick('input', null);
        $input->addClass('input');
        $input->attr(array(
            'type'  => 'date',
            'name'  => $this->name() . '-date-' . $type . '-visual',
            'id'    => $this->id() . '-date-' . $type . '-visual',
            'value' => ($this->mode == 'aggregation') ? $this->value[$type . 'date'] : '',
        ));
        $input->data(array(
            'field'  => 'date',
            'format' => 'YYYY-MM-DD',
            'i18n'   => html(json_encode(array(
                'previousMonth' => '&lsaquo;',
                'nextMonth'     => '&rsaquo;',
                'months'        => l::get('fields.date.months'),
                'weekdays'      => l::get('fields.date.weekdays'),
                'weekdaysShort' => l::get('fields.date.weekdays.short')
            )), false),
        ));

        /*
            Create hidden storage input field
         */
        $hidden = new Brick('input', null);
        $hidden->type  = 'hidden';
        $hidden->attr(array(
            'name'  => $this->name() . '-date-' . $type,
            'id'    => $this->id() . '-date-' . $type,
            'value' => ($this->mode == 'aggregation') ? $this->value[$type . 'date'] : '',
        ));
        return $input . $hidden;
    }

    /**
     * Generate limit input markup
     *
     * @return string
     */
    public function limitInput()
    {
        $input = new Brick('input', null);
        $input->addClass('input');
        $input->attr(array(
            'type'         => 'number',
            'name'         => $this->name() . '-limit',
            'autocomplete' => 'off',
            'id'           => $this->id() . '-limit',
            'placeholder'  => '#',
            'value'        => ($this->mode == 'aggregation') ? $this->value['limit'] : '',
            'min'          => 0,
            'step'         => 1,
        ));
        return $input;
    }

    /**
     * Generate storage input value
     *
     * @return string|null
     */
    public function storageValue()
    {
        if($this->mode == 'curation')
        {
            return rawurlencode(json_encode($this->value));
        }
        return null;
    }

    /**************************************************************************\
    *                           HANDLEBAR TEMPLATES                            *
    \**************************************************************************/

    /**
     * Load a handlebars template
     *
     * @param string $template
     * @return string
     */
    public function handlebarsTemplate($template)
    {
        return tpl::load(__DIR__ . DS . 'templates' . DS . $template . '.php', array('field' => $this));
    }

    /**************************************************************************\
    *                            DATA PREPARATIONS                             *
    \**************************************************************************/

    /**
     * Build config data structure for JS use
     *
     * @return array
     */
    public function config()
    {
        return array(
            'mode'            => $this->mode,
            'aggregationMode' => ($this->mode == 'aggregation'),
            'curationMode'    => ($this->mode == 'curation'),
            'texts'           => array(
                'addapage'        => l::get('curator.action.addapage'),
                'hidefilters'     => l::get('curator.action.hidefilters'),
            ),
        );
    }

    /**
     * Build pages data structure for JS use
     *
     * @return array
     */
    public function pages()
    {
        /*
            FIX: Set the active content language.
            Unfortunatly, this ugly fix has to be in place,
            otherwise Kirby would just return the default languages
            content.
         */
        site()->visit('/', site()->language()->code());

        /*
            Find available pages based on mode of operation
         */
        if($this->pages == 'children')
        {
            $pages = $this->page()->children();
        }
        else{
            $pages = site()->index();
        }

        /*
            Hide pages that shall be excluded
         */
        $pages = $pages->filter(function($item) {
            return !in_array($item->uri(), c::get('field.curator.exclude'));
        });

        /*
            Sort pages based on configuration
         */
        if($this->sort != 'default')
        {
            $pages = $pages->sortBy($this->sort, ($this->flip) ? 'desc' : 'asc');
        }

        /*
            Build data array
         */
        $data = array();
        foreach($pages as $page)
        {
            $thumbs = array();
            if($this->pageHasFigure($page))
            {
                $figure = $this->getFirstPageFigure($page);
                $thumbs['small'] = (string) thumb(
                                        $figure,
                                        array(
                                            'width' => 48,
                                            'height' => 48,
                                            'crop' => true
                                        ))->url();
                $thumbs['large'] = (string) thumb(
                                        $figure,
                                        array(
                                            'width' => 300,
                                            'height' => 200,
                                            'crop' => true
                                        ))->url();
            }

            $data[] = array(
                'uri'      => (string) $page->uri(),
                'template' => (string) $page->intendedTemplate(),
                'title'    => (string) $page->title()->html(),
                'texts'    => implode(' ', array(
                    $page->description(),
                    $page->caption(),
                    $page->text(),
                )),
                'tags'     => implode(',', array(
                    $page->tags(),
                    $page->type(),
                )),
                'date'     => (string) $page->date('Y-m-d'),
                'links'    => array(
                    'edit'    => purl($page, 'show'),
                    'preview' => (string) $page->url(),
                ),
                'thumbs'   => $thumbs,
                'selected' => ($this->mode == 'curation') ? in_array($page->uri(), $this->value) : false,
            );
        }
        return $data;
    }

    /**************************************************************************\
    *                              MISC. HELPERS                               *
    \**************************************************************************/

    /**
     * Check if a page has at least one figure attached
     *
     * @param \Page $page
     * @return bool
     */
    protected function pageHasFigure($page)
    {
        $figures = $this->getAllPageFigures($page);
        return count($figures) > 0;
    }

    /**
     * Find all figures attached to a page
     *
     * @param  Page $page
     * @return array
     */
    protected function getAllPageFigures($page)
    {
        if(isset($this->figuresCache[$page->uri()]))
        {
            return $this->figuresCache[$page->uri()];
        }

        $filenames = str::split($page->figure());
        $figures = array();
        foreach($filenames as $filename)
        {
            $file = $page->files()->find($filename);
            if(!is_null($file))
            {
                $figures[] = $file;
            }

        }
        $this->figuresCache[$page->uri()] = $figures;
        return $figures;
    }

    /**
     * Find the first figure attached to a page
     *
     * @param  Page $page
     * @return File
     */
    protected function getFirstPageFigure($page)
    {
        $figures = $this->getAllPageFigures($page);
        return $figures[0];
    }

}
