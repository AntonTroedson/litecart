<div class="row">
  <div class="col-lg-12">
    <h3><?php echo language::translate('title_campaign_products', 'Campaign Products'); ?></h3>
  </div>
</div>

<div class="row text-center">
  <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
</div>