
 <!--COMPONENTS CHANGE ADDRESS--> 		   
<component-cart-preview
ref="childref"
@set-cartcount="cartCount"
@after-drawerclose="afterDrawerclose"
cart_preview="<?php echo $cart_preview?>"
:drawer="drawer_preview_cart"
:payload="['items','subtotal','distance_local_new','merchant_info','go_checkout','items_count']"
:label="<?php 
echo CommonUtility::safeJsonEncode([
  'your_cart'=>t("Your cart from"),
  'summary'=>t("Summary"),
  'no_order'=>t("You don't have any orders here!"),
  'lets_change_that'=>t("let's change that!"),
  'cart'=>t("Cart"),
  'go_checkout'=>t("Go to checkout"),
  'free'=>t("Free")
])
?>"
>
</component-cart-preview> 
<!--END COMPONENTS CHANGE ADDRESS-->