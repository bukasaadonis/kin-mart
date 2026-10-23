<?php $promoItems=array_values(array_filter($items,fn($p)=>discount_percent($p)>0)); $featured=array_slice($items,0,8); ?>
<section class="shop-hero">
  <div class="hero-copy">
    <div class="hero-eyebrow">✦ Produits du quotidien en RDC · prix affichés en FC</div>
    <h1>Vos courses du quotidien, dans un univers plus beau et plus pratique.</h1>
    <p>Découvrez un catalogue plus visuel, des produits inspirés de la consommation locale en RDC et une expérience fluide pour commander en retrait ou en livraison.</p>
    <div class="hero-actions"><a class="btn btn-light btn-lg" href="<?=e(base_url('catalog'))?>">Faire mes courses</a><a class="btn btn-outline-light btn-lg" href="#categories">Explorer les rayons</a></div>
  </div>
  <div class="hero-showcase"><?php foreach(array_slice($categories,0,4) as $c):?><a class="hero-mini text-decoration-none" href="<?=e(base_url('catalog?category='.$c['id']))?>"><img src="<?=e(category_art_url($c['name']))?>" alt=""><span><?=e($c['name'])?> →</span></a><?php endforeach;?></div>
</section>
<div class="service-ribbon">
  <div class="service-item"><div class="service-icon">🚚</div><div><strong>Livraison pratique</strong><small>Choisissez votre zone au moment de commander.</small></div></div>
  <div class="service-item"><div class="service-icon">↻</div><div><strong>Retrait en magasin</strong><small>Commandez en ligne, récupérez chez KIN-MART.</small></div></div>
  <div class="service-item"><div class="service-icon">★</div><div><strong>Prix en Franc Congolais</strong><small>Lecture plus naturelle du catalogue et des ventes en FC.</small></div></div>
  <div class="service-item"><div class="service-icon">✓</div><div><strong>Stock visible</strong><small>Disponibilité affichée avant l’ajout au panier.</small></div></div>
</div>
<section id="categories" class="store-section">
  <div class="store-section-head"><div><h2>Achetez par rayon</h2><p>Les catégories les plus utiles, accessibles en un clic.</p></div><a class="section-link" href="<?=e(base_url('catalog'))?>">Voir tous les produits →</a></div>
  <div class="category-grid"><?php foreach(array_slice($categories,0,14) as $c):?><a class="category-card" href="<?=e(base_url('catalog?category='.$c['id']))?>"><img src="<?=e(category_art_url($c['name']))?>" alt=""><strong><?=e($c['name'])?></strong></a><?php endforeach;?></div>
</section>
<section class="store-section" id="promos">
  <div class="promo-banner"><div><span class="promo-pill">OFFRES KIN-MART</span><strong class="d-block mt-2">Un catalogue plus réaliste avec des visuels produits sur fond blanc.</strong><p>Les produits mis en avant reflètent désormais davantage les habitudes de consommation locales.</p></div><a class="btn btn-light" href="<?=e(base_url('catalog'))?>">Découvrir les offres</a></div>
</section>
<section class="store-section">
  <div class="store-section-head"><div><h2>Les essentiels KIN-MART</h2><p>Nouveautés, meilleures ventes et produits disponibles maintenant.</p></div><a class="section-link" href="<?=e(base_url('catalog'))?>">Catalogue complet →</a></div>
  <div class="product-grid"><?php foreach($featured as $p): $disc=discount_percent($p);?><article class="shop-product"><a class="text-decoration-none text-dark" href="<?=e(base_url('product/'.$p['id']))?>"><div class="product-media"><img src="<?=e(product_image_url($p))?>" alt="<?=e($p['name'])?>"><div class="product-badges"><?php if($disc):?><span class="promo-badge">-<?=$disc?>%</span><?php endif;?><span class="fresh-badge"><?=e($p['category'])?></span></div></div><div class="product-body"><div class="product-category"><?=e($p['category'])?></div><div class="product-title"><?=e($p['name'])?></div><div class="product-meta"><span class="stock-chip">En stock</span><span><?=e($p['available_qty'])?> dispo.</span></div><div class="product-price"><span class="now"><?=money($p['effective_price'])?></span><?php if($disc):?><span class="was"><?=money($p['sale_price'])?></span><?php endif;?></div></div></a><form method="post" action="<?=e(base_url('cart/add'))?>" class="product-action"><?=csrf_field()?><input type="hidden" name="product_id" value="<?=$p['id']?>"><input type="hidden" name="qty" value="1"><input type="hidden" name="return_to" value=""><button class="btn btn-soft">🛒 Ajouter au panier</button></form></article><?php endforeach;?></div>
</section>
<section class="store-section mb-4"><div class="row g-3"><div class="col-lg-4"><div class="km-card p-4 h-100"><span class="small-caps">Rapide</span><h4 class="fw-800 mt-2">Une recherche pensée pour les courses</h4><p class="text-muted mb-0">Cherchez par nom, par rayon ou par besoin du quotidien et accédez rapidement au bon produit.</p></div></div><div class="col-lg-4"><div class="km-card p-4 h-100"><span class="small-caps">Flexible</span><h4 class="fw-800 mt-2">Livraison ou retrait</h4><p class="text-muted mb-0">Commandez en ligne puis choisissez le mode de réception le plus pratique pour vous.</p></div></div><div class="col-lg-4"><div class="km-card p-4 h-100"><span class="small-caps">Personnel</span><h4 class="fw-800 mt-2">Vos habitudes, mieux servies</h4><p class="text-muted mb-0">Une navigation plus soignée aide à retrouver plus vite vos produits les plus utiles.</p></div></div></div></section>
