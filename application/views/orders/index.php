<div class='mainContent'>
<?php if(isset($returnMessage)) echo $returnMessage; ?><br />
<?php  
if(count($orders) > 0){?>
	Review your current orders:
	<?php foreach($orders as $order): ?>
		<br />
		Seller: <?=anchor('user/'.$order['seller']['userHash'], $order['seller']['userName']);?><br />
		<?php foreach($order['items'] as $item): ?>
			<li><?=$item['quantity'];?> x <?=anchor('item/'.$item['itemHash'], $item['name']);?> <br />
		<?php endforeach; ?>
		Total Price: <?=$order['currencySymbol'].$order['totalPrice'];?><br />
		Progress: <?=$order['progress']?><br />
		
	<?php endforeach; 
} else { ?>
You have no orders at present.<br />
<?php } ?>

</div>