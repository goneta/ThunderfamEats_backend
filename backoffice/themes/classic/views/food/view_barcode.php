<div class="printhis_barcode">
  <?php if($barcodes):?>
    <div class="text-right p-3">
      <button class="print_barcode btn btn-green">Print</button>
    </div>
    <?php foreach ($barcodes as $items):?>        
       <div class="mb-3" style="display: table;">
        <div class="d-flex align-items-end border p-2">
            <div>
                <h4 style="margin-bottom: 2px;" class="text-uppercase font-weight-bolder"><?php echo $items['item_name']?></h4>
                <div class="ellipsis-2-lines" style="max-width: 200px;"><?=$items['description'] ?? ''?></div>
                <?php if($items['barcode_url']):?>
                <img src="<?=$items['barcode_url']?>" style="max-width: 200px;"/>
                <?php endif;?>
                <div><?=$items['barcode']?></div>
            </div>
            <div class="pl-2">
                <p class="barcode-price">
                   <span class="whole-price"><?php echo $items['price1']?></span>
                   <span class="decimal-price"><?php echo $items['price2']?></span>
                </p>
            </div>
        </div>
       </div>
    <?php endforeach;?>      
  <?php endif;?>
</div>