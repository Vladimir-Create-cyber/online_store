<?php echo $header;
$col = $column_left ? 9 : 12;
$col = $column_right ? $col - 3 : $col; ?>



<style>
    section.litele-form .title {
        margin-top: 0;
    }

    #form-amocrm {
        margin-bottom: 60px;
    }

    .downloads .checkout__button-box.but2 a {f
        background: #ffffff;
        color: #343434;
    }

    .downloads .checkout__button-box.but2 a:hover {
        color: #2a77ed;
    }

    .checkout__button-box .btn {
        min-width: 255px !important;
        cursor: pointer;
    }

    .downloads .but2 a:hover svg.icon-arrow-long-right {
        fill: #2a77ed;
    }

    .downloads .but2 a svg.icon-arrow-long-right {
        fill: #fff;
    }
</style>


<main class="content">
	<div class="container">
	<?php echo $content_top; ?>

    <div class="breadcrumbs breadcrumbs--sm-pad">
            
            <!-- breadcrumbs -->

			<ul class="breadcrumb__list">
			<?php foreach ($breadcrumbs as $i=> $breadcrumb) { ?>
				<?php if($i == 0) { ?>
					<li <?php echo $schema ? 'itemscope itemtype="http://schema.org/BreadcrumbList"' : ''?> class="breadcrumb__list-item"><a href="<?php echo $breadcrumb['href']; ?>" <?php echo $schema ? 'itemprop="url"' : ''?>><span <?php echo $schema ? 'itemprop="title"' : ''?>><?php echo $breadcrumb['text']; ?></span></a></li>
				<?php } elseif($i + 1 < count($breadcrumbs)) { ?>
					<li <?php echo $schema ? 'itemscope itemtype="http://schema.org/BreadcrumbList"' : ''?> class="breadcrumb__list-item"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg> <a class="js-popup-call-hover" href="<?php echo $breadcrumb['href']; ?>" <?php echo $schema ? 'itemprop="url"' : ''?>><span <?php echo $schema ? 'itemprop="title"' : ''?>><?php echo $breadcrumb['text']; ?></span></a></li>
				<?php } else { ?>
					<li class="breadcrumb__list-item"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg><?php echo $breadcrumb['text']; ?></li>		
				<?php } ?>
			<?php } ?> 			
			</ul>
				<?php foreach ($breadcrumbs as $i=> $breadcrumb) { ?>
					<div class="popup" id="categories-popup<?php echo $i; ?>">
						<div class="nav-submenu nav-submenu--min">
							<ul class="nav-submenu__list">
					<?php if(isset($breadcrumb['breadList'])) { ?>
						<?php foreach ($breadcrumb['breadList'] as $breadlist) { ?>
							<li class="nav-submenu__list-item"><a href="<?php echo $breadlist['href']; ?>" class="nav-submenu__link"><?php echo $breadlist['name']; ?></a></li>
						<?php }  ?>
					<?php }  ?>
							</ul>
						</div>
					</div>
				<?php } ?> 	
		</div>

            <!-- breadcrumbs -->

            <h1 class="categories__title js-cat-title-height"> <?= $heading_title_meta;?></h1>
            
            <?php dump($categories);?>
            <?php if ($categories) { ?>

            <div class="categories">

                <div class="categories__inner">

                        <?php if (!$products && $category_categories) { ?>
                        <div class="categories__heading categories__heading--col-<?php echo $category_categories; ?> <?php echo count($categories) == 1 ? 'categories-heading--fix' : ''?>">

                            <?php if ($description) { ?>
                            <div class="categories__heading-text">
                                <div class="categories__heading-text-inner js-custom-scroll">					
                                    <div class="editor description_top_1">
                                        <?php echo $description; ?>	
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?> 
            
                    
                    <div class="row">
                            
                        <?php if ($category_categories) { ?>
                            <?php foreach ($categories as $i=> $category) { ?>
                            <?php if($i < ($category_categories == 2 ? 3 : 2)) { ?>
                            <div class="col-md-<?php echo $category_categories; ?> col-sm-4 col-xs-6">
                                <div class="categories__item categories__item--col-<?php echo $category_categories; ?>">
                                    <a href="<?php echo $category['href']; ?>" class="categories__link js-animate" style="background-image: url(<?php echo $category['thumb']; ?>); <?php echo $category_background ? '' : 'background-size: contain;'?>">
                                        <div class="categories__caption js-animate-caption">
                                            <span class="categories__caption-text">
                                                <?php echo $category['name']; ?>
                                            </span>
                                        </div>
                                        <span class="categories-overlay"></span>
                                    </a>
                                </div>
                            </div>
                            <?php } ?>
                            <?php } ?>

                            <?php if (!$products) { ?>
                            <div class="<?php echo count($categories) == 1 ? 'col-md-8' : 'col-md-' . ($category_categories == 4 ? 4 : 6) ?>  col-sm-4 col-xs-6 categories__item-heading">
                                <div class="categories__item categories__item--col-<?php echo $category_categories; ?>"></div>
                            </div>
                            <?php } ?>

                            <?php foreach ($categories as $i=> $category) { ?>
                            <?php if($i >= ($category_categories == 2 ? 3 : 2)) { ?>
                            <div class="col-md-<?php echo $category_categories; ?> col-sm-4 col-xs-6">
                                <div class="categories__item categories__item--col-<?php echo $category_categories; ?>">
                                    <a href="<?php echo $category['href']; ?>" class="categories__link js-animate" style="background-image: url(<?php echo $category['thumb']; ?>); <?php echo $category_background ? '' : 'background-size: contain;'?>">
                                        <div class="categories__caption js-animate-caption">
                                            <span class="categories__caption-text">
                                                <?php echo $category['name']; ?>
                                            </span>
                                        </div>
                                        <span class="categories-overlay"></span>
                                    </a>
                                </div>
                            </div>				
                            <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    
                    
                </div>
                
            </div>
        <?php } ?>
    </div>
</div>