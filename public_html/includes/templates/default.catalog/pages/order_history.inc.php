<div class="twelve-eighty">
  <!--snippet:notices-->

  <h1 class="title"><?php echo language::translate('title_order_history', 'Order History'); ?></h1>
  <table class="table table-striped data-table">
    <thead>
    <tr>
      <th class="main"><?php echo language::translate('title_order', 'Order'); ?></th>
      <th class="text-center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
      <th class="text-center"><?php echo language::translate('title_date', 'Date'); ?></th>
      <th class="text-center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php if ($orders) foreach($orders as $order) { ?>
    <tr>
      <td><a href="<?php echo htmlspecialchars($order['link']); ?>" class="fancybox"><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></a></td>
      <td class="text-center"><?php echo $order['order_status']; ?></td>
      <td class="text-right"><?php echo $order['date_created']; ?></td>
      <td class="text-right"><?php echo $order['payment_due']; ?></td>
    </tr>
    <?php } ?>
    </tbody>
  </table>

  <?php echo $pagination; ?>
</div>