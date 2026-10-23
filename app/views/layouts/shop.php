<?php
$cartCount=array_sum($_SESSION['shop_cart']??[]);
try{$navCategories=(new App\Models\Product())->categories();}catch(Throwable $e){$navCategories=[];}
$currentQ=trim($_GET['q']??'');
$user=auth_user();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#d71920"><title><?=e($title??'KIN-MART Super Marché')?></title><link rel="icon" href="<?=e(asset('icons/favicon.svg'))?>"><link rel="stylesheet" href="<?=e(asset('vendor/bootstrap/bootstrap.min.css'))?>"><link rel="stylesheet" href="<?=e(asset('css/app.css'))?>"></head><body>
<div class="shop-utility"><div class="container"><div class="utility-points"><span>✓ Qualité & fraîcheur</span><span>↻ Retrait en magasin</span><span>🚚 Livraison à Kinshasa</span></div><div>Besoin d’aide ? <strong>Service client KIN-MART</strong></div></div></div>
<header class="shop-header">
  <div class="container shop-header-main">
    <a class="shop-brand" href="<?=e(base_url())?>"><img src="<?=e(company_logo_url())?>" alt="KIN-MART"></a>
    <form class="shop-search" method="get" action="<?=e(base_url('catalog'))?>"><span class="search-icon">⌕</span><input class="form-control" name="q" value="<?=e($currentQ)?>" placeholder="Rechercher un produit, une marque, une catégorie…" aria-label="Rechercher"><button class="btn btn-km" type="submit">Rechercher</button></form>
    <div class="shop-actions">
      <a class="shop-action account-action" href="<?=e($user?(($user['role']??'')==='CLIENT'?base_url('account'):base_url('admin')):base_url('login'))?>"><span>◎</span><span class="label"><?=$user?e(explode(' ',trim($user['full_name']??'Mon compte'))[0]):'Compte'?></span></a>
      <a class="shop-action cart-action" href="<?=e(base_url('cart'))?>"><span>🛒</span><span class="label">Panier</span><span class="cart-count"><?=$cartCount?></span></a>
    </div>
  </div>
  <div class="shop-cats"><div class="container"><a class="shop-cat-link featured" href="<?=e(base_url('catalog'))?>">☰ Tous les rayons</a><a class="shop-cat-link" href="<?=e(base_url('catalog'))?>#promos">🔥 Promotions</a><?php foreach(array_slice($navCategories,0,9) as $c):?><a class="shop-cat-link" href="<?=e(base_url('catalog?category='.$c['id']))?>"><?=e($c['name'])?></a><?php endforeach;?></div></div>
</header>
<main class="container store-shell"><?php if($m=flash('success')):?><div class="alert alert-success auto-dismiss rounded-4 border-0 shadow-sm"><?=e($m)?></div><?php endif;?><?php if($m=flash('error')):?><div class="alert alert-danger auto-dismiss rounded-4 border-0 shadow-sm"><?=e($m)?></div><?php endif;?><?php require $contentView;?></main>
<footer class="shop-footer"><div class="container footer-top"><div class="footer-brand"><img src="<?=e(company_logo_url())?>" alt="KIN-MART"><p>Votre super marché au quotidien : courses, promotions, fidélité, retrait et livraison depuis une seule plateforme.</p></div><div class="footer-col"><h6>Acheter</h6><a href="<?=e(base_url('catalog'))?>">Tous les produits</a><a href="<?=e(base_url('catalog'))?>#promos">Promotions</a><a href="<?=e(base_url('cart'))?>">Mon panier</a></div><div class="footer-col"><h6>Services</h6><a href="<?=e(base_url('checkout'))?>">Livraison</a><a href="<?=e(base_url('checkout'))?>">Retrait en magasin</a><a href="<?=e(base_url('account'))?>">Fidélité KIN-MART</a></div><div class="footer-col"><h6>Mon KIN-MART</h6><a href="<?=e(base_url('login'))?>">Se connecter</a><a href="<?=e(base_url('register'))?>">Créer un compte</a><a href="<?=e(base_url('account'))?>">Mes commandes</a></div></div><div class="container footer-bottom"><span>© <?=date('Y')?> KIN-MART Super Marché</span><span>Paiements : Espèces · Carte · M-Pesa · Airtel Money · Orange Money</span></div></footer>
<nav class="mobile-shop-bottom"><a href="<?=e(base_url())?>"><b>⌂</b>Accueil</a><a href="<?=e(base_url('catalog'))?>"><b>⌕</b>Produits</a><a href="<?=e(base_url('cart'))?>"><b>🛒</b>Panier <?=$cartCount?'('.$cartCount.')':''?></a><a href="<?=e($user?base_url('account'):base_url('login'))?>"><b>◎</b>Compte</a></nav>
<script src="<?=e(asset('vendor/bootstrap/bootstrap.bundle.min.js'))?>"></script><script src="<?=e(asset('js/app.js'))?>"></script></body></html>
