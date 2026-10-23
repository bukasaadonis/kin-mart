<?php
$primaryCards=[
 ['label'=>'CA aujourd’hui','value'=>money($stats['today']),'hint'=>'Ventes validées ce jour','tone'=>'red','icon'=>'↗'],
 ['label'=>'CA semaine','value'=>money($stats['week']),'hint'=>'Performance sur 7 jours','tone'=>'slate','icon'=>'◫'],
 ['label'=>'CA mois','value'=>money($stats['month']),'hint'=>'Mois en cours','tone'=>'gold','icon'=>'◎'],
 ['label'=>'Marge du mois','value'=>money($stats['profit']),'hint'=>'Marge brute estimée','tone'=>'green','icon'=>'◆'],
];
$opsCards=[
 ['label'=>'Tickets du jour','value'=>$stats['sales'],'sub'=>'Caisse & commandes finalisées','icon'=>'🧾'],
 ['label'=>'Commandes actives','value'=>$stats['orders'],'sub'=>'À préparer ou livrer','icon'=>'⏳'],
 ['label'=>'Clients actifs','value'=>$stats['clients'],'sub'=>'Base clients actuelle','icon'=>'👥'],
 ['label'=>'Valeur du stock','value'=>money($stats['stock_value']),'sub'=>'Valorisation au coût d’achat','icon'=>'📦'],
 ['label'=>'Stock faible','value'=>$stats['low'],'sub'=>'Produits sous le seuil minimum','icon'=>'⚠️'],
 ['label'=>'Ruptures','value'=>$stats['out'],'sub'=>'Articles indisponibles','icon'=>'⛔'],
 ['label'=>'Expire < 30 jours','value'=>$stats['expiring'],'sub'=>'Surveillance péremption','icon'=>'⏱️'],
 ['label'=>'Références actives','value'=>$stats['products'],'sub'=>'Catalogue disponible','icon'=>'🏷️'],
];
$topCount=count($top);
$alertCount=count($notifs);
$topRevenue=0;
foreach($top as $x){$topRevenue+=(float)($x['revenue']??0);} 
$avgTicket = ($stats['sales'] ?? 0) > 0 ? ((float)($stats['today'] ?? 0) / (float)$stats['sales']) : 0;
$stockHealth = ($stats['products'] ?? 0) > 0 ? max(0, min(100, round((1 - ((float)($stats['low'] ?? 0) / max(1, (float)$stats['products']))) * 100))) : 100;
?>
<section class="hero-board km-reveal">
  <div class="hero-copy-block">
    <div class="eyebrow">KIN-MART · centre de pilotage</div>
    <h2><?=e(km_greeting())?>, <?=e(explode(' ',trim($_SESSION['user']['full_name']??'Gestionnaire'))[0])?>.</h2>
    <p>Vision opérationnelle claire du supermarché : ventes en FC, état des stocks, suivi des commandes et signaux à traiter sans surcharge visuelle.</p>
    <div class="hero-actions-row">
      <a class="btn btn-km" href="<?=e(base_url('pos'))?>">Ouvrir la caisse</a>
      <a class="btn btn-soft" href="<?=e(base_url('products'))?>">Gérer le catalogue</a>
      <a class="btn btn-soft" href="<?=e(base_url('analytics'))?>">Vue analytique</a>
    </div>
    <div class="hero-glance-row">
      <div class="glance-chip"><span>Santé stock</span><strong><?=e($stockHealth)?>%</strong></div>
      <div class="glance-chip"><span>Ticket moyen</span><strong><?=money($avgTicket)?></strong></div>
      <div class="glance-chip"><span>Taux paramétré</span><strong>1 USD = <?=number_format((float)(company_settings()['usd_cdf_rate'] ?? 2850),0,',',' ')?> CDF</strong></div>
    </div>
  </div>
  <div class="hero-side-stats">
    <div class="hero-mini-card"><span>Alertes en attente</span><strong><?=e($alertCount)?></strong><small>centre de surveillance</small></div>
    <div class="hero-mini-card"><span>Top produits</span><strong><?=e($topCount)?></strong><small>best-sellers du moment</small></div>
    <div class="hero-mini-card"><span>Recettes top produits</span><strong><?=money($topRevenue)?></strong><small>sur le tableau de performance</small></div>
    <div class="hero-mini-card"><span>Catalogue actif</span><strong><?=e($stats['products'])?> références</strong><small>catalogue localisé RDC</small></div>
  </div>
</section>

<div class="primary-kpis km-reveal">
  <?php foreach($primaryCards as $card):?>
  <article class="kpi-card tone-<?=e($card['tone'])?>">
    <div class="kpi-head"><div class="kpi-label"><?=e($card['label'])?></div><div class="kpi-icon"><?=e($card['icon'])?></div></div>
    <div class="kpi-value"><?=e($card['value'])?></div>
    <div class="kpi-hint"><?=e($card['hint'])?></div>
  </article>
  <?php endforeach;?>
</div>

<div class="ops-grid km-reveal">
  <?php foreach($opsCards as $item):?>
  <article class="ops-card">
    <div class="ops-top"><span class="ops-icon"><?=e($item['icon'])?></span><div class="ops-value"><?=e($item['value'])?></div></div>
    <div class="ops-label"><?=e($item['label'])?></div>
    <div class="ops-sub"><?=e($item['sub'])?></div>
  </article>
  <?php endforeach;?>
</div>

<div class="focus-strip km-reveal mb-3">
  <div class="focus-card"><span>Focus du jour</span><strong>Rayons sensibles</strong><small>Surveille les produits en rupture, à faible stock ou proches de péremption.</small></div>
  <div class="focus-card"><span>Encaissement</span><strong>Pilotage en Franc Congolais</strong><small>Tous les indicateurs de vente sont présentés en FC pour une lecture locale plus naturelle.</small></div>
  <div class="focus-card"><span>Expérience</span><strong>Catalogue visuel enrichi</strong><small>Le front-office utilise désormais des visuels produits plus réalistes sur fond blanc.</small></div>
</div>

<div class="dashboard-grid dashboard-grid-modern mb-3 km-reveal">
  <div class="km-card panel-card chart-panel-modern">
    <div class="panel-head modern-head">
      <div>
        <div class="small-caps">Tendance</div>
        <h5>Ventes des 7 derniers jours</h5>
        <div class="small text-muted">Évolution quotidienne du chiffre d’affaires</div>
      </div>
      <span class="status-pill success">Mise à jour continue</span>
    </div>
    <div class="chart-box"><canvas id="dailyChart"></canvas></div>
  </div>
  <div class="km-card panel-card activity-panel">
    <div class="panel-head modern-head">
      <div>
        <div class="small-caps">Surveillance</div>
        <h5>Alertes & activité</h5>
        <div class="small text-muted">Résumé des éléments récents à traiter</div>
      </div>
      <a class="small text-decoration-none fw-bold text-km" href="<?=e(base_url('notifications'))?>">Tout voir</a>
    </div>
    <?php if(!$notifs):?>
      <div class="empty-state-soft"><div class="empty-icon">✓</div><strong>Aucune alerte récente</strong><div class="small text-muted mt-1">Le centre de contrôle est stable pour le moment.</div></div>
    <?php endif;?>
    <?php foreach($notifs as $n):?>
      <div class="feed-item">
        <div class="feed-dot"></div>
        <div><strong><?=e($n['title'])?></strong><small><?=e($n['message'])?></small></div>
      </div>
    <?php endforeach;?>
  </div>
</div>

<div class="dashboard-grid dashboard-grid-modern km-reveal">
  <div class="km-card panel-card">
    <div class="panel-head modern-head">
      <div>
        <div class="small-caps">Performance produits</div>
        <h5>Produits les plus vendus</h5>
        <div class="small text-muted">Volume écoulé et chiffre d’affaires généré</div>
      </div>
      <a class="small text-decoration-none fw-bold text-km" href="<?=e(base_url('analytics'))?>">Analyser</a>
    </div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Produit</th><th>Quantité</th><th>Chiffre d’affaires</th></tr></thead><tbody><?php foreach($top as $i=>$x):?><tr><td><span class="rank-pill"><?=($i+1)?></span></td><td><strong><?=e($x['name'])?></strong></td><td><?=e($x['qty'])?></td><td><strong><?=money($x['revenue'])?></strong></td></tr><?php endforeach;?></tbody></table></div>
  </div>
  <div class="km-card panel-card chart-panel-modern">
    <div class="panel-head modern-head">
      <div>
        <div class="small-caps">Encaissements</div>
        <h5>Modes de paiement</h5>
        <div class="small text-muted">Répartition des règlements enregistrés</div>
      </div>
    </div>
    <div class="chart-box"><canvas id="payChart"></canvas></div>
  </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{const red='#c8382f',slate='#243240',gold='#b88746',green='#1e7d61',grid='rgba(52,58,67,.08)';new Chart(document.getElementById('dailyChart'),{type:'line',data:{labels:<?=json_encode(array_column($daily,'label'))?>,datasets:[{data:<?=json_encode(array_map('floatval',array_column($daily,'value')))?>,borderColor:red,backgroundColor:'rgba(200,56,47,.08)',fill:true,tension:.35,borderWidth:3,pointRadius:4,pointBackgroundColor:'#fff',pointBorderColor:red,pointBorderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:grid}}}}});new Chart(document.getElementById('payChart'),{type:'doughnut',data:{labels:<?=json_encode(array_column($payments,'method'))?>,datasets:[{data:<?=json_encode(array_map('floatval',array_column($payments,'value')))?>,backgroundColor:[red,slate,gold,green,'#4d7dbb','#7b5db5','#d79a3e'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'70%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8,padding:16}}}}})})</script>
