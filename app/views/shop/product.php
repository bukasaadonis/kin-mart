<?php $disc=discount_percent($product); $available=(float)$product['quantity']-(float)$product['reserved_quantity']; ?>
<div class="breadcrumb-lite"><a class="text-decoration-none" href="<?=e(base_url())?>">Accueil</a> / <a class="text-decoration-none" href="<?=e(base_url('catalog'))?>">Produits</a> / <?=e($product['category'])?></div>
<div class="product-detail">
  <div class="product-gallery"><img src="<?=e(product_image_url($product))?>" alt="<?=e($product['name'])?>"></div>
  <section class="product-info">
    <div class="d-flex align-items-center gap-2 mb-2"><span class="badge-soft-dark badge"><?=e($product['category'])?></span><?php if($disc):?><span class="promo-badge">-<?=$disc?>%</span><?php endif;?></div>
    <h1><?=e($product['name'])?></h1>
    <?php if(!empty($product['brand'])):?><div class="text-muted">Marque : <strong class="text-dark"><?=e($product['brand'])?></strong></div><?php endif;?>
    <div class="product-price-lg"><span class="now"><?=money($product['effective_price']??$product['sale_price'])?></span><?php if($disc):?><span class="was"><?=money($product['sale_price'])?></span><span class="badge badge-km">Économisez <?=money((float)$product['sale_price']-(float)$product['effective_price'])?></span><?php endif;?></div>
    <div class="availability-box">✓ <span><strong>Disponible chez KIN-MART</strong><br><small><?=$available?> <?=e($product['unit'])?> actuellement en stock</small></span></div>
    <p class="text-muted mt-4 mb-0"><?=nl2br(e($product['description']??'Produit sélectionné et disponible chez KIN-MART Super Marché.'))?></p>
    <div class="purchase-box"><div class="small-caps mb-2">Ajouter à votre panier</div><form method="post" action="<?=e(base_url('cart/add'))?>" class="d-flex flex-wrap gap-2 align-items-center"><?=csrf_field()?><input type="hidden" name="product_id" value="<?=$product['id']?>"><input class="form-control quantity-field" type="number" min="1" max="<?=max(1,(int)$available)?>" name="qty" value="1"><input type="hidden" name="return_to" value="cart"><button class="btn btn-km btn-lg flex-grow-1">🛒 Ajouter au panier</button></form><?php if(($_SESSION['user']['role']??'')==='CLIENT'):?><form method="post" action="<?=e(base_url('favorite'))?>" class="mt-2"><?=csrf_field()?><input type="hidden" name="product_id" value="<?=$product['id']?>"><button class="btn btn-soft w-100">♡ Ajouter à mes favoris</button></form><?php endif;?></div>
    <div class="benefit-list"><div class="benefit"><strong>↻ Retrait magasin</strong>Commandez puis récupérez vos courses chez KIN-MART.</div><div class="benefit"><strong>🚚 Livraison</strong>Frais calculés selon la zone choisie.</div><div class="benefit"><strong>★ Fidélité</strong>Gagnez des points sur vos achats identifiés.</div><div class="benefit"><strong>✓ Prix transparent</strong>Le prix affiché est repris dans votre panier.</div></div>
  </section>
</div>
