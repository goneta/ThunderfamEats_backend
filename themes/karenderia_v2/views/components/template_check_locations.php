<script type="text/x-template" id="xtemplate_check_locations">
<template v-if="out_of_range">
    <h4><?php echo t("You're out of range")?></h4>
    <p><?php echo t("This restaurant cannot deliver to your locations.")?></p>
    <el-button @click="changeLocation" round><?php echo t("Change locations")?></el-button>
</template>
</script>