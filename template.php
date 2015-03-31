<input class="js-curator-storage" type="hidden" name="<?= $field->name() ?>" id="<?= $field->name() ?>" value="<?php echo $field->storageValue() ?>" />

<!-- Include config and pages data structures -->
<script>
    if(typeof CuratorConfig == 'undefined') {
        CuratorConfig = {};
    }
    CuratorConfig['<?= $field->name() ?>'] = <?= json_encode($field->config()); ?>;

    if(typeof CuratorItems == 'undefined') {
        CuratorItems = {};
    }
    CuratorItems['<?= $field->name() ?>'] = <?= json_encode($field->pages()); ?>;
</script>

<!-- Include Handlebars templates for later use -->
<?= $field->handlebarsTemplate('filtered-list') ?>
<?= $field->handlebarsTemplate('selected-list') ?>

<!-- Filters headline -->
<div class="field field-grid-item field-with-headline">
    <h2 class="hgroup hgroup-single-line hgroup-compressed cf">
        <span class="hgroup-title">
            <?php echo $field->i18n($field->label) ?>
        </span>
        <?php if($field->mode == 'curation'): ?>
            <span class="hgroup-options shiv shiv-left">
                <a class="hgroup-option-right [ curator-filter-toggle js-curator-filter-toggle ]" href="#">
                    <i class="icon icon-left fa fa-plus-circle"></i>
                    <span><?php echo l::get('curator.action.addapage') ?></span>
                </a>
            </span>
        <?php endif ?>
    </h2>
</div>

<!-- Type field -->
<div class="field field-with-icon field-grid-item field-grid-item-1-2 [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.type') ?>
    </label>
    <div class="field-content">
        <?= $field->typeInput() ?>
        <div class="field-icon">
            <i class="icon fa fa-chevron-down"></i>
        </div>
    </div>
</div>

<!-- Root Page field -->
<div class="field field-with-icon field-grid-item field-grid-item-1-2 [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.root') ?>
    </label>
    <div class="field-content">
        <?= $field->rootPageInput() ?>
        <div class="field-icon">
            <i class="icon fa fa-chain"></i>
        </div>
    </div>
</div>

<!-- Search field -->
<div class="field field-with-icon field-grid-item [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.keyword') ?>
    </label>
    <div class="field-content">
        <?= $field->searchInput() ?>
        <div class="field-icon">
            <i class="icon fa fa-search"></i>
        </div>
    </div>
</div>

<!-- Tags field -->
<div class="field field-with-icon field-grid-item [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.tags') ?>
    </label>
    <div class="field-content">
        <?= $field->tagsInput() ?>
        <div class="field-icon">
            <i class="icon fa fa-tag"></i>
        </div>
    </div>
</div>

<!-- From Date field -->
<div class="field field-with-icon field-grid-item field-grid-item-1-2 [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.date.from') ?>
    </label>
    <div class="field-content">
        <?= $field->dateInput('from') ?>
        <div class="field-icon">
            <i class="icon fa fa-calendar"></i>
        </div>
    </div>
</div>

<!-- To Date field -->
<div class="field field-with-icon field-grid-item field-grid-item-1-2 [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.date.to') ?>
    </label>
    <div class="field-content">
        <?= $field->dateInput('to') ?>
        <div class="field-icon">
            <i class="icon fa fa-calendar"></i>
        </div>
    </div>
</div>

<!-- Limit field -->
<div class="field field-with-icon field-grid-item field-grid-item-1-2 [ js-curator-filter ]">
    <label class="label">
        <?php echo l::get('curator.filter.limit') ?>
    </label>
    <div class="field-content">
        <?= $field->limitInput() ?>
        <div class="field-icon">
            <i class="icon fa fa-scissors"></i>
        </div>
    </div>
</div>

<?php if($field->mode == 'aggregation'): ?>
    <div class="field field-grid-item field-with-headline [ js-curator-filter ]">
        <h2 class="hgroup hgroup-single-line hgroup-compressed cf">
            <span class="hgroup-title">
                <?php echo $field->label() ?>
                <?php echo l::get('curator.results.title.aggregation') ?>
            </span>
        </h2>
    </div>
<?php endif ?>

<!-- Filtered list -->
<div class="field field-grid-item  [ js-curator-filter ]">
    <?php if($field->mode == 'curation'): ?>
        <label class="label">
            <?php echo l::get('curator.results.title.curation') ?>
        </label>
    <?php endif ?>
    <div class="curator-results-zone js-curator-results-zone"></div>
</div>

<!-- Visual separator -->
<div class="field field-grid-item  [ js-curator-filter ]">
    <hr class="curator-separator" />
</div>

<!-- Selected list -->
<?php if($field->mode == 'curation'): ?>
    <div class="field field-grid-item">
        <div class="curator-selected-zone js-curator-selected-zone"></div>
    </div>
<?php endif ?>
