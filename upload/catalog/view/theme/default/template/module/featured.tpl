<h3><?php echo $heading_title; ?></h3>
<div class="row">
  <?php foreach ($products as $product) { ?>
  <div class="product-layout col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="product-thumb transition">
      <div class="image"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" class="img-responsive" /></div>
         <div class="caption">
             <h4><?php echo $product['name']; ?></h4>
             <table >
               <?php foreach ($product['attribute_groups'] as $attribute_group) { ?>
               <tbody>
                 <?php foreach ($attribute_group['attribute'] as $attribute) { ?>
                 <tr>
                   <td><?php echo $attribute['name']; ?></td>
                   <?php if ($attribute['name'] == 'Price Retail') { ?>
                     <td><b><span style="text-decoration: line-through;"><?php echo $attribute['text']; ?></span></b></td>
                   <?php } elseif ($attribute['name'] == 'Sale Price') { ?>
                     <td><b><?php echo $attribute['text']; ?></b></td>
                   <?php } else { ?>
                     <td><b><?php echo $attribute['text']; ?></b></td>
                   <?php } ?>
                 </tr>
                 <?php } ?>
               </tbody>
               <?php } ?>
             </table>
         </div>
    </div>
  </div>
  <?php } ?>
</div>
