<div class="list-group">
  <?php foreach ($categories as $category) { ?>
   <?php if ($category['category_id'] == $category_id) { ?>
      <a href="<?php echo $category['href']; ?>" class="list-group-item active"><?php echo $category['name']; ?></a>
      <?php if ($category['children']) { ?>
      <?php foreach ($category['children'] as $child) { ?>
         <?php if ($child['category_id'] == $child_id) { ?>
            <a href="<?php echo $child['href']; ?>" class="list-group-item active">&nbsp;&nbsp;&nbsp;- <?php echo $child['name']; ?></a>
            <?php if ($child['children']) { ?>
               <?php foreach ($child['children'] as $child2) { ?>
                  <?php if ($child2['category_id'] == $child2_id) { ?>
                     <a href="<?php echo $child2['href']; ?>" class="list-group-item active">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php echo $child2['name']; ?></a>
                  <?php } else { ?>
                     <a href="<?php echo $child2['href']; ?>" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-<?php echo $child2['name']; ?></a>
                  <?php } ?>      
               <?php } ?>
            <?php } ?>
         <?php } else { ?>
            <a href="<?php echo $child['href']; ?>" class="list-group-item">&nbsp;&nbsp;&nbsp;-<?php echo $child['name']; ?></a>
         <?php } ?>
      <?php } ?>
   <?php } ?>
  <?php } else { ?>
   <a href="<?php echo $category['href']; ?>" class="list-group-item"><?php echo $category['name']; ?></a>
  <?php } ?>
  <?php } ?>
</div>
