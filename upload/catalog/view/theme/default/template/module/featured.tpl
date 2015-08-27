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
               
               <tr><td colspan=2>
               <div id="floatingenquire<?php echo $product['product_id']; ?>"> 
                  <div id="tab"><b>Click To Enquire</b></div> 
                  <div id="contactform"> 
                     <div class="entry">
                     <form method="post" action="1771george.php"> 
                     <table> 
                        <tr> <td valign="top"><label for="first_name">First Name *</label> </td> </tr>
                        <tr> <td valign="top"><input type="text" name="first_name" maxlength="50" size="30"> </td> </tr> 
                        <tr> <td valign="top""><label for="last_name">Last Name *</label> </td></tr>
                        <tr> <td valign="top"> <input type="text" name="last_name" maxlength="50" size="30"> </td> </tr> 
                        <tr> <td valign="top"> <label for="email">Email Address *</label> </td> </tr> 
                        <tr> <td valign="top"> <input type="text" name="email" maxlength="80" size="30"> </td> </tr> 
                        <tr> <td valign="top"> <label for="telephone">Telephone Number</label> </td> </tr>
                        <tr> <td valign="top"> <input type="text" name="telephone" maxlength="30" size="30"> </td> </tr> 
                        <tr> <td valign="top"> <label for="comments">Message *</label> </td> </tr>
                        <tr> <td valign="top"> <textarea name="comments" maxlength="1000" cols="25" rows="6"></textarea> </td> </tr> 
                        <tr> <td colspan="2" style="text-align:center"> <input type="submit" value="Submit"> </td> </tr> 
                     </table>
                     </form>
                     </div> 
                  </div> 
               </div>
               <style> 
                  #floatingenquire<?php echo $product['product_id']; ?> #contactform {border: 1px solid black; border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;background-color:#eff3fa;display:none;} 
                  #floatingenquire<?php echo $product['product_id']; ?> #contactform .sbutton {clear:both;margin:5px 5px 0 5px;} </style> 
                  <script> 
                     var flip<?php echo $product['product_id']; ?> = 0; 
                     $("#floatingenquire<?php echo $product['product_id']; ?> #tab").click(function () { 
                        flip<?php echo $product['product_id']; ?>++; 
                        if (flip<?php echo $product['product_id']; ?> % 2 == 1) 
                           $("#floatingenquire<?php echo $product['product_id']; ?> #contactform").slideDown('slow'); 
                        else $("#floatingenquire<?php echo $product['product_id']; ?> #contactform").slideUp('slow'); 
                        }); 
                  </script>
               </style>
               </td></tr>
             </table>
         </div>
    </div>
  </div>
  <?php } ?>
</div>
