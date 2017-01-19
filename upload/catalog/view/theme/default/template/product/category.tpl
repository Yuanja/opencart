<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <h2><?php echo $heading_title; ?></h2>
      <?php if ($thumb || $description) { ?>
      <div class="row">
        <?php if ($thumb) { ?>
        <div class="col-sm-2"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" title="<?php echo $heading_title; ?>" class="img-thumbnail" /></div>
        <?php } ?>
        <?php if ($description) { ?>
        <div class="col-sm-10"><?php echo $description; ?></div>
        <?php } ?>
      </div>
      <hr>
      <?php } ?>
      <?php if ($categories) { ?>
      <h3><?php echo $text_refine; ?></h3>
      <?php if (count($categories) <= 5) { ?>
      <div class="row">
        <div class="col-sm-3">
          <ul>
            <?php foreach ($categories as $category) { ?>
            <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
      <?php } else { ?>
      <div class="row">
        <?php foreach (array_chunk($categories, ceil(count($categories) / 4)) as $categories) { ?>
        <div class="col-sm-3">
          <ul>
            <?php foreach ($categories as $category) { ?>
            <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
            <?php } ?>
          </ul>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
      <?php } ?>
      <?php if ($products) { ?>
      <div class="row">
        <div class="col-md-4">
          <div class="btn-group hidden-xs">
            <button type="button" id="grid-view" class="btn btn-default" data-toggle="tooltip" title="<?php echo $button_grid; ?>"><i class="fa fa-th"></i></button>
            <button type="button" id="list-view" class="btn btn-default" data-toggle="tooltip" title="<?php echo $button_list; ?>"><i class="fa fa-th-list"></i></button>
          </div>
        </div>
        <div class="col-md-2 text-right">
          <label class="control-label" for="input-sort"><?php echo $text_sort; ?></label>
        </div>
        <div class="col-md-3 text-right">
          <select id="input-sort" class="form-control" onchange="location = this.value;">
            <?php foreach ($sorts as $sorts) { ?>
            <?php if ($sorts['value'] == $sort . '-' . $order) { ?>
            <option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-1 text-right">
          <label class="control-label" for="input-limit"><?php echo $text_limit; ?></label>
        </div>
        <div class="col-md-2 text-right">
          <select id="input-limit" class="form-control" onchange="location = this.value;">
            <?php foreach ($limits as $limits) { ?>
            <?php if ($limits['value'] == $limit) { ?>
            <option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      <br />
      <div class="row">
        <?php foreach ($products as $product) { ?>
        <div class="product-layout product-list col-xs-12">
          <div class="product-thumb">
            <div class="image"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" class="img-responsive" /></div>
            <div>
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
                    <?php if (isset($product['description']) && !empty($product['description'])) { ?>
                       <tr>
                         <td style="vertical-align: top"><b>Notes<b></td>
                         <td><?php echo $product['description']; ?></td>
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
                        <?php if ($site_key) { ?>
                            <tr> <td valign="top"><div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div> </tr>
                        <?php } ?>
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
        </div>
        <?php } ?>
      </div>
      <div class="row">
        <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
        <div class="col-sm-6 text-right"><?php echo $results; ?></div>
      </div>
      <?php } ?>
      <?php if (!$categories && !$products) { ?>
      <p><?php echo $text_empty; ?></p>
      <div class="buttons">
        <div class="pull-right"><a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
      </div>
      <?php } ?>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>
