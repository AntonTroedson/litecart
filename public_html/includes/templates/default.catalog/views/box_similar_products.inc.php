<h3><?php echo language::translate('title_similar_products', 'Similar Products'); ?></h3>

<div class="products row half-gutter text-center">
  <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
</div>