<?php if (count($products)): ?>
    <div class="qas_grid">
    <?php foreach ($products as $p) : ?><div class="qas_card_wrapper">
        <div class="qas_card">
            <div class="qas_card_img_wrapper">
                <a href="<?php echo esc_url($p->url) ?>" rel="nofollow" target="_blank">
                    <img src="<?php echo esc_url($p->image ? $p->image : plugins_url('public/img/no-image.png', dirname(dirname(__FILE__)))) ?>" alt="<?php echo esc_attr($p->name) ?>">
                </a>
                <?php if (isset($p->savingPercent)) : ?>
                <span class="qas_card_saving_percent">-<?php echo esc_html($p->savingPercent) ?>%</span>
                <?php endif ?>
            </div>
            <div class="qas_card_info">
                <h5>
                    <a href="<?php echo esc_url($p->url) ?>" rel="nofollow" target="_blank"><?php echo esc_html(wp_trim_words($p->name, 5, '')) ?></a>
                </h5>
                <?php if ($showPrice) : ?>
                <div class="qas_card_info_price"><?php echo esc_html($p->price) ?> <?php echo $this->currencyCodeToSymbol($p->currency) ?><?php if (isset($p->strikedPrice)) : ?> <span class="qas_card_info_striked_price"><?php echo esc_html($p->strikedPrice) ?> <?php echo $this->currencyCodeToSymbol($p->currency) ?></span><?php endif ?></div>
                <?php endif ?>
                <a href="<?php echo esc_url($p->url) ?>" class="button qas_card_info_button" rel="nofollow" target="_blank">
                    <?php echo esc_html($buttonText); ?>
                </a>
            </div>
        </div>
    </div><?php endforeach ?>
<?php else: ?>
    <?php _e('Could not find any offer corresponding to search criteria.', 'quick-affiliate-store') ?>
<?php endif ?>