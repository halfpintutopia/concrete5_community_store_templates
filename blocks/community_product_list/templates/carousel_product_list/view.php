<?php
defined('C5_EXECUTE') or die("Access Denied.");

// We want to crop the image so we're providing an object
// with the crop property set to true since default is false
$legacyThumbProps = new \stdClass();
$legacyThumbProps->crop = true;
$communityStoreImageHelper = $app->make('cs/helper/image', ['product_list', null, $legacyThumbProps]);
$csm = $app->make('cs/helper/multilingual');

$c = \Concrete\Core\Page\Page::getCurrentPage();


$columnClass = 'col-md-12';

if ($productsPerRow == 2) {
    $columnClass = 'col-md-6';
}

if ($productsPerRow == 3) {
    $columnClass = 'col-md-4';
}

if ($productsPerRow == 4) {
    $columnClass = 'col-md-3';
}

if ($productsPerRow == 6) {
    $columnClass = 'col-md-2';
}

?>

<style>
    @media (min-width: 768px) {

        /* show 3 items */
        .carousel-inner .active,
        .carousel-inner .active+.carousel-item,
        .carousel-inner .active+.carousel-item+.carousel-item {
            display: block;
        }

        .carousel-inner .carousel-item.active:not(.carousel-item-right):not(.carousel-item-left),
        .carousel-inner .carousel-item.active:not(.carousel-item-right):not(.carousel-item-left)+.carousel-item,
        .carousel-inner .carousel-item.active:not(.carousel-item-right):not(.carousel-item-left)+.carousel-item+.carousel-item {
            transition: none;
        }

        .carousel-inner .carousel-item-next,
        .carousel-inner .carousel-item-prev {
            position: relative;
            transform: translate3d(0, 0, 0);
        }

        .carousel-inner .active.carousel-item+.carousel-item+.carousel-item+.carousel-item {
            position: absolute;
            top: 0;
            right: -33.3333%;
            z-index: -1;
            display: block;
            visibility: visible;
        }

        /* left or forward direction */
        .active.carousel-item-left+.carousel-item-next.carousel-item-left,
        .carousel-item-next.carousel-item-left+.carousel-item,
        .carousel-item-next.carousel-item-left+.carousel-item+.carousel-item,
        .carousel-item-next.carousel-item-left+.carousel-item+.carousel-item+.carousel-item {
            position: relative;
            transform: translate3d(-100%, 0, 0);
            visibility: visible;
        }

        /* farthest right hidden item must be abso position for animations */
        .carousel-inner .carousel-item-prev.carousel-item-right {
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            display: block;
            visibility: visible;
        }

        /* right or prev direction */
        .active.carousel-item-right+.carousel-item-prev.carousel-item-right,
        .carousel-item-prev.carousel-item-right+.carousel-item,
        .carousel-item-prev.carousel-item-right+.carousel-item+.carousel-item,
        .carousel-item-prev.carousel-item-right+.carousel-item+.carousel-item+.carousel-item {
            position: relative;
            transform: translate3d(100%, 0, 0);
            visibility: visible;
            display: block;
            visibility: visible;
        }

    }
</style>

<div class="carousel-product-list-wrapper">
    <div class="container">
        <div class="store-product-list-block">
            <?php if ($products) { ?>
                <?php if ($showSortOption) { ?>
                    <div class="store-product-list-sort row">
                        <div class="col-md-12 form-inline text-right pull-right">
                            <div class="form-group">
                                <?= $form->label('sort' . $bID, t('Sort by')); ?>
                                <?= $form->select(
                                    'sort' . $bID,
                                    [
                                        '0' => '',
                                        'price_asc' => t('price, lowest to highest'),
                                        'price_desc' => t('price, highest to lowest'),
                                    ]
                                ); ?>
                            </div>
                        </div>
                    </div>

                    <script type="text/javascript">
                        $(function() {
                            $('#sort<?= $bID; ?>').change(function() {
                                let sortstring = '<?= $app->make('helper/url')->setVariable(['sort' . $bID => '%sort%']); ?>';
                                window.location.href = sortstring.replace('%sort%', $(this).val());
                            });
                        });
                    </script>
                <?php } ?>

                <div id="productistCarousel" class="carousel slide" data-ride="carousel" data-interval="12000">
                    <div class="carousel-inner w-100 mx-auto flex-nowrap store-product-list row store-product-list-per-row-<?= $productsPerRow ?>" role="listbox">

                        <?php
                        $i = 1;
                        $itemActive = true;

                        foreach ($products as $product) {
                            $options = $product->getOptions();

                            $variationLookup = $product->getVariationLookup();
                            $variationData = $product->getVariationData();
                            $availableOptionsids = $variationData['availableOptionsids'];
                            $firstAvailableVariation = $variationData['firstAvailableVariation'];

                            if ($firstAvailableVariation) {
                                $product = $firstAvailableVariation;
                            } else {
                                $product->setInitialVariation();
                            }

                            $product->setPriceAdjustment($variationData['priceAdjustment']);

                            $isSellable = $product->isSellable();

                            //this is done so we can get a type of active class if there's a product list on the product page
                            if ($c->getCollectionID() == $product->getPageID()) {
                                $activeclass = 'on-product-page';
                            }

                            $productPage = $product->getProductPage();

                            if (!$productPage || $productPage->isError() || $productPage->isInTrash()) {
                                $productPage = false;
                            } ?>

                            <div class="carousel-item store-product-list-item <?= $columnClass; ?> <?= $activeclass; ?> <?= $itemActive ? 'active' : '' ?>">
                                <form id="store-form-add-to-cart-list-<?= $product->getID(); ?>" data-product-id="<?= $product->getID(); ?>">
                                    <?= $token->output('community_store'); ?>
                                    <?php if ($showName) { ?>
                                        <h2 class="store-product-list-name"><?= $csm->t($product->getName(), 'productName', $product->getID()); ?></h2>
                                    <?php } ?>

                                    <?php
                                    $imgObj = $product->getImageObj();
                                    if (is_object($imgObj)) {
                                        $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                                    ?>
                                        <p class="store-product-list-thumbnail">
                                            <?php if ($showQuickViewLink) { ?>
                                                <a class="store-product-quick-view" data-product-id="<?= $product->getID(); ?>" data-locale="<?= $locale; ?>" href="#">
                                                    <img src="<?= $thumb->src; ?>" class="img-responsive">
                                                </a>
                                            <?php } elseif ($showPageLink && $productPage) { ?>
                                                <a href="<?= \Concrete\Core\Support\Facade\Url::to($productPage); ?>">
                                                    <img src="<?= $thumb->src; ?>" class="img-responsive">
                                                </a>
                                            <?php } else { ?>
                                                <img src="<?= $thumb->src; ?>" class="img-responsive">
                                            <?php } ?>
                                        </p>
                                    <?php } // if is_obj 
                                    ?>
                                    <?php if ($showPrice && !$product->allowCustomerPrice()) {
                                        $salePrice = $product->getSalePrice();
                                        $price = $product->getPrice();
                                        $activePrice = ($salePrice ? $salePrice : $price) - $product->getPriceAdjustment($product->getDiscountRules());
                                    ?>
                                        <p class="store-product-price store-product-list-price" data-price="<?= $activePrice; ?>" data-original-price="<?= ($salePrice ? $price : ''); ?>">
                                            <?php
                                            $salePrice = $product->getSalePrice();
                                            if (isset($salePrice) && "" != $salePrice) {
                                                $formattedSalePrice = $product->getFormattedSalePrice();
                                                $formattedOriginalPrice = $product->getFormattedOriginalPrice();
                                                echo '<span class="store-sale-price">' . $formattedSalePrice . '</span>';
                                                echo '&nbsp;' . t('was') . '&nbsp;' . '<span class="store-original-price">' . $formattedOriginalPrice . '</span>';
                                            } else {
                                                $formattedPrice = $product->getFormattedPrice();
                                                echo $formattedPrice;
                                            }
                                            ?>
                                        </p>
                                    <?php } ?>

                                    <?php if ($product->allowCustomerPrice()) { ?>
                                        <div class="store-product-customer-price-entry form-group">
                                            <?php
                                            $pricesuggestions = $product->getPriceSuggestionsArray();
                                            if (!empty($pricesuggestions)) {
                                            ?>
                                                <p class="store-product-price-suggestions"><?php
                                                                                            foreach ($pricesuggestions as $suggestion) {
                                                                                            ?>
                                                        <a href="#" class="store-price-suggestion btn btn-default btn-sm" data-suggestion-value="<?= $suggestion; ?>" data-add-type="list"><?= Config::get('community_store.symbol') . $suggestion; ?></a>
                                                    <?php } ?>
                                                </p>
                                                <label for="customerPrice" class="store-product-customer-price-label"><?= t('Enter Other Amount'); ?></label>
                                            <?php } else { ?>
                                                <label for="customerPrice" class="store-product-customer-price-label"><?= t('Amount'); ?></label>
                                            <?php } ?>
                                            <?php $min = $product->getPriceMinimum(); ?>
                                            <?php $max = $product->getPriceMaximum(); ?>
                                            <div class="input-group col-md-6 col-sm-6 col-xs-6">
                                                <div class="input-group-addon"><?= Config::get('community_store.symbol'); ?></div>
                                                <input type="number" <?= $min ? 'min="' . $min . '"' : ''; ?> <?= $max ? 'max="' . $max . '"' : ''; ?> step="0.01" class="store-product-customer-price-entry-field form-control" value="<?= $product->getPrice(); ?>" name="customerPrice" />
                                            </div>
                                            <?php if ($min >= 0 || $max > 0) { ?>
                                                <span class="store-min-max help-block">
                                                    <?php
                                                    if (!is_null($min)) {
                                                        echo t('minimum') . ' ' . Config::get('community_store.symbol') . $min;
                                                    }

                                                    if (!is_null($max)) {
                                                        if ($min >= 0) {
                                                            echo ', ';
                                                        }
                                                        echo t('maximum') . ' ' . Config::get('community_store.symbol') . $max;
                                                    }
                                                    ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>


                                    <?php if ($showDescription) { ?>
                                        <div class="store-product-list-description"><?= $csm->t($product->getDesc(), 'productDescription', $product->getID()); ?></div>
                                    <?php } ?>
                                    <?php if ($showPageLink && $productPage) { ?>
                                        <p class="store-btn-more-details-container"><a href="<?= \Concrete\Core\Support\Facade\Url::to($productPage); ?>" class="store-btn-more-details btn btn-default"><?= ($pageLinkText ? $pageLinkText : t("More Details")); ?></a></p>
                                    <?php } ?>
                                    <?php if ($showAddToCart) { ?>
                                        <div class="store-product-options">
                                            <?php if ($product->allowQuantity() && $showQuantity) { ?>
                                                <div class="store-product-quantity form-group">
                                                    <label class="store-product-option-group-label"><?= t('Quantity'); ?></label>

                                                    <?php $quantityLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID()); ?>

                                                    <?php if ($quantityLabel) { ?>
                                                        <div class="input-group">
                                                        <?php } ?>
                                                        <?php $max = $product->getMaxCartQty(); ?>

                                                        <?php if ($product->allowDecimalQuantity()) { ?>
                                                            <input type="number" name="quantity" class="store-product-qty form-control" min="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" step="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                                        <?php } else { ?>
                                                            <input type="number" name="quantity" class="store-product-qty form-control" value="1" min="1" step="1" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                                        <?php } ?>

                                                        <?php if ($quantityLabel) { ?>
                                                            <div class="input-group-addon"><?= $quantityLabel; ?></div>
                                                        </div>
                                                    <?php } ?>

                                                </div>
                                            <?php } else { ?>
                                                <input type="hidden" name="quantity" class="store-product-qty" value="1">
                                            <?php } ?>

                                            <?php
                                            foreach ($product->getOptions() as $option) {
                                                $optionItems = $option->getOptionItems();
                                                $optionType = $option->getType();
                                                $required = $option->getRequired();
                                                $displayType = $option->getDisplayType();
                                                $details = $option->getDetails();

                                                $requiredAttr = '';

                                                if ($required) {
                                                    $requiredAttr = ' required="required" placeholder="' . t('Required') . '" ';
                                                }
                                            ?>

                                                <?php if (!$optionType || $optionType == 'select') { ?>
                                                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                                        <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                                        <?php if ($details) { ?>
                                                            <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                                        <?php } ?>

                                                        <?php if ($displayType != 'radio') { ?>
                                                            <select class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> form-control" name="po<?= $option->getID(); ?>">
                                                            <?php } ?>
                                                            <?php
                                                            $firstAvailableVariation = false;
                                                            $variation = false;
                                                            $disabled = false;
                                                            $outOfStock = false;
                                                            $firstOptionItem = true;
                                                            foreach ($optionItems as $optionItem) {
                                                                if (!$optionItem->isHidden()) {
                                                                    $variation = $variationLookup[$optionItem->getID()];
                                                                    $selected = '';

                                                                    if (!empty($variation)) {
                                                                        $firstAvailableVariation = (!$firstAvailableVariation && $variation->isSellable()) ? $variation : $firstAvailableVariation;
                                                                        $disabled = $variation->isSellable() ? '' : 'disabled="disabled" ';
                                                                        $outOfStock = $variation->isSellable() ? '' : ' (' . t('out of stock') . ')';

                                                                        if (is_array($availableOptionsids) && in_array($optionItem->getID(), $availableOptionsids)) {
                                                                            $selected = 'selected="selected"';
                                                                        }
                                                                    } else {
                                                                        if ($firstOptionItem) {
                                                                            $selected = 'selected="selected"';
                                                                            $firstOptionItem = false;
                                                                        }
                                                                    }

                                                                    $optionLabel = $optionItem->getName();
                                                                    $translateHandle = 'optionValue';

                                                                    if ($optionItem->getSelectorName()) {
                                                                        $optionLabel = $optionItem->getSelectorName();
                                                                        $translateHandle = 'optionSelectorName';
                                                                    }

                                                            ?>

                                                                    <?php if ($displayType == 'radio') { ?>
                                                                        <div class="radio">
                                                                            <label><input type="radio" required class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> " <?= $disabled . ($selected ? 'checked' : ''); ?> name="po<?= $option->getID(); ?>" value="<?= $optionItem->getID(); ?>" data-adjustment="<?= (float) $optionItem->getPriceAdjustment($product->getDiscountRules()); ?>" /><?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock; ?>

                                                                            </label>
                                                                        </div>
                                                                    <?php } else { ?>
                                                                        <option <?= $disabled . ' ' . $selected; ?> value="<?= $optionItem->getID(); ?>" data-adjustment="<?= (float) $optionItem->getPriceAdjustment($product->getDiscountRules()); ?>"><?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock; ?></option>
                                                                    <?php } ?>

                                                                <?php } ?>
                                                            <?php } ?>
                                                            <?php if ($displayType != 'radio') { ?>
                                                            </select>
                                                        <?php } ?>
                                                    </div>
                                                <?php } elseif ($optionType == 'text') { ?>
                                                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                                        <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                                        <?php if ($details) { ?>
                                                            <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                                        <?php } ?>

                                                        <input class="store-product-option-entry form-control" <?= $requiredAttr; ?> name="pt<?= $option->getID(); ?>" />
                                                    </div>
                                                <?php } elseif ($optionType == 'textarea') { ?>
                                                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                                        <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                                        <?php if ($details) { ?>
                                                            <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                                        <?php } ?>

                                                        <textarea class="store-product-option-entry form-control" <?= $requiredAttr; ?> name="pa<?= $option->getID(); ?>"></textarea>
                                                    </div>
                                                <?php } elseif ($optionType == 'checkbox') { ?>
                                                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                                        <label class="store-product-option-group-label">
                                                            <input type="hidden" value="<?= t('no'); ?>" class="store-product-option-checkbox-hidden <?= $option->getHandle(); ?>" name="pc<?= $option->getID(); ?>" />
                                                            <input type="checkbox" value="<?= t('yes'); ?>" class="store-product-option-checkbox <?= $option->getHandle(); ?>" name="pc<?= $option->getID(); ?>" /> <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                                        <?php if ($details) { ?>
                                                            <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                                        <?php } ?>

                                                    </div>
                                                <?php } elseif ($optionType == 'hidden') { ?>
                                                    <input type="hidden" class="store-product-option-hidden <?= $option->getHandle(); ?>" name="ph<?= $option->getID(); ?>" />
                                                <?php } elseif ($optionType == 'static') { ?>
                                                    <div class="store-product-option-static">
                                                        <?= $csm->t($details, 'optionDetails', $product->getID(), $option->getID()); ?>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>

                                        <input type="hidden" name="pID" value="<?= $product->getID(); ?>">

                                        <p class="store-btn-add-to-cart-container">
                                            <button data-add-type="list" data-product-id="<?= $product->getID(); ?>" class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden'); ?> "><?= ($btnText ? h($btnText) : t("Add to Cart")); ?></button>
                                        </p>
                                        <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : ''); ?>"><?= t("Out of Stock"); ?></p>

                                    <?php } ?>

                                    <?php if (count($product->getOptions()) > 0) { ?>
                                        <script>
                                            <?php
                                            $varationData = [];
                                            foreach ($variationLookup as $key => $variation) {
                                                $product->setVariation($variation);
                                                $product->setPriceAdjustment(0);
                                                $imgObj = $product->getImageObj();

                                                if ($imgObj) {
                                                    $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                                                }

                                                $varationData[$key] = [
                                                    'price' => $product->getPrice(),
                                                    'salePrice' => $product->getSalePrice(),
                                                    'available' => ($variation->isSellable()),
                                                    'imageThumb' => $thumb ? $thumb->src : '',
                                                    'image' => $imgObj ? $imgObj->getRelativePath() : '',
                                                    'saleTemplate' => '<span class="store-sale-price"></span>&nbsp;' . t('was') . '&nbsp;<span class="store-original-price"></span>'
                                                ];

                                                if ($isWholesale) {
                                                    $varationData[$key]['price'] = $product->getWholesalePrice();
                                                }
                                            } ?>

                                            var variationData = variationData || [];
                                            variationData[<?= $product->getID(); ?>] = <?= json_encode($varationData); ?>;
                                        </script>
                                    <?php } ?>

                                </form><!-- .product-list-item-inner -->
                            </div><!-- .product-list-item -->

                            <!-- <?php if ($i % $productsPerRow ==  0) { ?>
                                </div>
                                <div class="store-product-list row store-product-list-per-row-<?php $productsPerRow ?>">
                            <?php } ?> -->

                            <?php ++$i; ?>
                            <?php $itemActive = false; ?>
                        <?php } // foreach 
                        ?>
                    </div><!-- .product-list -->

                    <!-- PREV and NEXT  -->
                    <a class="carousel-control-prev" href="#productistCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#productistCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>

                </div> <!-- .carousel -->

                <script>
                    jQuery(function($) {
                        $('#productistCarousel').on('slide.bs.carousel', function(e) {

                            var $e = $(e.relatedTarget);
                            var idx = $e.index();
                            var itemsPerSlide = 4;
                            var totalItems = $('.carousel-item').length;

                            if (idx >= totalItems - (itemsPerSlide - 1)) {
                                var it = itemsPerSlide - (totalItems - idx);
                                for (var i = 0; i < it; i++) {
                                    // append slides to end
                                    if (e.direction == "left") {
                                        $('.carousel-item').eq(i).appendTo('.carousel-inner');
                                    } else {
                                        $('.carousel-item').eq(0).appendTo('.carousel-inner');
                                    }
                                }
                            }
                        });
                    });
                </script>

                <?php if ($showPagination) { ?>
                    <?php if ($paginator->getTotalPages() > 1) { ?>
                        <div class="row">
                            <?= $pagination ?>
                        </div>
                    <?php } ?>
                <?php } ?>

            <?php } elseif (is_object($c) && $c->isEditMode()) { ?>
                <div class="ccm-edit-mode-disabled-item"><?= t("Empty Product List"); ?></div>
            <?php } elseif ($noProductsMessage) { ?>
                <p class="alert alert-info"><?= h($noProductsMessage); ?></p>
            <?php } ?>
        </div>
    </div>
</div>