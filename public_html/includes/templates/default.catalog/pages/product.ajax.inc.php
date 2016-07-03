<?php
  document::$snippets['javascript'][] = 'if (window.location.hash != \'\') $(\'a[href="\' + window.location.hash + \'"]\').click();';
?>
{snippet:breadcrumbs}

<div id="product">
  <div class="row">
    <div class="col-sm-4">
      <div class="image thumbnail">
        <a href="<?php echo htmlspecialchars($image['original']); ?>" data-toggle="lightbox" data-gallery="product">
          <img class="img-responsive" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($name); ?>" />
          <?php echo $sticker; ?>
        </a>
      </div>

      <?php if ($extra_images) { ?>
      <div class="extra-images row">
        <?php foreach ($extra_images as $image) { ?>
        <div class="extra-image col-xs-4">
          <div class="thumbnail">
            <a href="<?php echo htmlspecialchars($image['original']); ?>" data-toggle="lightbox" data-gallery="product">
              <img class="img-responsive" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($name); ?>" />
            </a>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>

    <div class="col-sm-8">
      <div class="row">
        <div class="col-md-6">
          <h1 class="page-title"><?php echo $name; ?></h1>

          <?php if ($description) { ?>
          <p><?php echo $description; ?></p>
          <?php } ?>
        </div>

        <div class="col-md-6">
          <div class="well">
            <h2 class="price-wrapper" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
              <?php if ($campaign_price) { ?>
              <del class="regular-price"><?php echo $regular_price; ?></del> <strong class="campaign-price" itemprop="price"><?php echo $campaign_price; ?></strong>
              <?php } else { ?>
              <span class="price" itemprop="price"><?php echo $regular_price; ?></span>
              <?php } ?>
            </h2>

            <div class="tax" style="margin: 0 0 1em 0;">
             <?php if ($tax_rates) { ?>
              <?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?><br />
              <?php echo implode('<br />', $tax_rates); ?>
             <?php } else { ?>
              <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
             <?php } ?>
            </div>

            <?php if ($cheapest_shipping_fee !== null) { ?>
            <div class="cheapest-shipping" style="margin: 1em 0;">
              <?php echo functions::draw_fonticon('fa-truck'); ?> <?php echo strtr(language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">%price</strong>'), array('%price' => currency::format($cheapest_shipping_fee))); ?>
            </div>
            <?php } ?>

            <div class="stock-status" style="margin: 1em 0;">
             <?php if ($quantity > 0) { ?>
              <div class="stock-available">
                <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
                <span class="value"><?php echo $stock_status_value; ?></span>
              </div>
              <?php if ($delivery_status_value) { ?>
              <div class="stock-delivery">
                <?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>:
                <span class="value"><?php echo $delivery_status_value;?></span>
              </div>
              <?php } ?>
             <?php } else { ?>
              <?php if ($sold_out_status_value) { ?>
                <div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>">
                  <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
                  <span class="value"><?php echo $sold_out_status_value; ?></span>
                </div>
              <?php } else { ?>
                <div class="stock-unavailable">
                  <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
                  <span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span>
                </div>
              <?php } ?>
             <?php } ?>
            </div>

            <hr />

            <div class="buy_now" style="margin: 1em 0;">
              <?php echo functions::form_draw_form_begin('buy_now_form', 'post'); ?>
              <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

              <?php if ($options) { ?>
                <?php foreach ($options as $option) { ?>
                <div class="form-group">
                  <label><?php echo $option['name']; ?></label>
                  <?php echo ($option['description'] ? '<div>' . $option['description'] . '</div>' : ''); ?>
                  <?php echo $option['values']; ?>
                </div>
                <?php } ?>
              <?php } ?>

              <?php if (!$catalog_only_mode) { ?>
              <div class="form-group">
                <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
                <table>
                  <tr>
                    <td class="input-group">
                      <?php echo (!empty($quantity_unit_decimals)) ? functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit_decimals, 1, null, 'data-size="small"') : (functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, 1)); ?>
                      <?php echo $quantity_unit_name ? '<div class="input-group-addon">'. $quantity_unit_name .'</div>' : ''; ?>
                      <?php //echo ($quantity > 0 || $orderable) ? functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit') : functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit', 'disabled="disabled"'); ?>
                    </td>
                    <td style="padding-left: 1em;">
                      <?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity <= 0 && !$orderable) ? ' disabled="disabled"' : '') .' style="display: table-cell;">'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
                    </td>
                  </tr>
                </table>
              </div>
              <?php } ?>

              <?php echo functions::form_draw_form_end(); ?>
            </div>

            <hr />

            <h4 class="social-bookmarks text-center" style="margin: 1em 0;">
            <?php echo language::translate('title_share', 'Share'); ?>:
              <a class="twitter" href="<?php echo document::href_link('http://twitter.com/home/', array('status' => $name .' - '. document::link())); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
              <a class="facebook" href="<?php echo document::href_link('http://www.facebook.com/sharer.php', array('u' => document::link())); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
              <a class="googleplus" href="<?php echo document::href_link('https://plus.google.com/share', array('url' => document::link())); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Google+'); ?>"><?php echo functions::draw_fonticon('fa-google-plus-square fa-lg', 'style="color: #dd4b39;"'); ?></a>
              <a class="pinterest" href="<?php echo document::href_link('http://pinterest.com/pin/create/button/', array('url' => document::link())); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
            </h4>
          </div>
        </div>
      </div>

      <?php if ($attributes) { ?>
      <div class="attributes">
        <table class="table table-striped">
<?php
  for ($i=0; $i<count($attributes); $i++) {
    if (strpos($attributes[$i], ':') !== false) {
      @list($key, $value) = explode(':', $attributes[$i]);
      echo '  <tr>' . PHP_EOL
         . '    <td>'. trim($key) .':</td>' . PHP_EOL
         . '    <td>'. trim($value) .'</td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    } else if (trim($attributes[$i]) != '') {
      echo '  <thead>' . PHP_EOL
         . '    <tr>' . PHP_EOL
         . '      <th colspan="2">'. $attributes[$i] .'</th>' . PHP_EOL
         . '    </tr>' . PHP_EOL
         . '  </thead>' . PHP_EOL
         . '  <tbody>' . PHP_EOL;
    } else {
      echo ' </tbody>' . PHP_EOL
         . '</table>' . PHP_EOL
         . '<table class="table table-striped">' . PHP_EOL;
    }
  }
?>
        </table>
      </div>
      <?php } ?>
    </div>
  </div>
</div>