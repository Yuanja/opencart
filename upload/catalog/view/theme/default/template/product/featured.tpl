<!DOCTYPE html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="catalog/view/javascript/jquery/jquery-2.1.1.min.js" type="text/javascript"></script>
<link href="catalog/view/javascript/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" />
<script src="catalog/view/javascript/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<link href="catalog/view/javascript/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,300,700" rel="stylesheet" type="text/css" />
<link href="catalog/view/theme/default/stylesheet/stylesheet.css" rel="stylesheet">
<?php foreach ($styles as $style) { ?>
<link href="<?php echo $style['href']; ?>" type="text/css" rel="<?php echo $style['rel']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script src="catalog/view/javascript/common.js" type="text/javascript"></script>
<?php foreach ($scripts as $script) { ?>
<script src="<?php echo $script; ?>" type="text/javascript"></script>
<?php } ?>
</head>
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
                      <?php if ($attribute['name'] == 'Sale Price') { ?>
                        <td><font style="color:red"><b><?php echo $attribute['name']; ?><b></font></td>
                      <?php } else { ?>
                        <td><b><?php echo $attribute['name']; ?><b></td>
                      <?php } ?> 
                      <?php if ($attribute['name'] == 'Retail Price') { ?>
                        <td><b><span style="text-decoration: line-through;"><?php echo $attribute['text']; ?></span></b></td>
                      <?php } elseif ($attribute['name'] == 'Sale Price') { ?>
                        <?php if ($product['stock_status'] == 'In Stock') { ?>
                           <td><font style="color:red"><b><?php echo $attribute['text']; ?></b></font></td>
                        <?php } else { ?>
                           <td>On Hold</td>
                        <?php } ?>
                      <?php } else { ?>
                        <td><?php echo $attribute['text']; ?></td>
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
                     <form method="post" action="<?php echo $enquire_link ?>"> 
                     <table> 
                        <tr> <td valign="top"> <label for="name">Name *</label> </td> </tr>
                        <tr> <td valign="top"> <input type="text" name="name"/></td> </tr>
                        <tr> <td valign="top"> <label for="email">Email *</label> </td> </tr>
                        <tr> <td valign="top"> <input type="text" name="email"/></td> </tr>
                        <tr> <td valign="top"> <label for="comments">Message *</label> </td> </tr>
                        <tr> <td valign="top"> <textarea name="enquiry" maxlength="1000" cols="25" rows="6"></textarea> </td> </tr> 
                        <tr> <td colspan="2" style="text-align:center"> <input type="submit" value="Submit"> </td> </tr> 
                     </table>
                     <input type="hidden" name="product_name" value="<?php echo $product['name'] ?>"/>
                     <input type="hidden" name="product_link" value="<?php echo $product['href'] ?>"/>
                     </form>
                     </div> 
                  </div> 
               </div>
               <style> 
                  #floatingenquirebak<?php echo $product['product_id']; ?> #contactform {border: 1px solid black; border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;background-color:#eff3fa;display:none;} 
                  #floatingenquire<?php echo $product['product_id']; ?> #contactform {display:none;}

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
