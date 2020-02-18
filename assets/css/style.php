<style type="text/css">
    table.mpc-wrap, table.mpc-wrap tr, table.mpc-wrap th, table.mpc-wrap td {
        border: 0;
    }
    table.mpc-wrap thead th {
        background: #444;
        color: #efefef;
        padding: .75em 1em;
    }
    table.mpc-wrap tbody tr td {
        padding: 1em;
        vertical-align: middle;
        color: #333;
    }
    table.mpc-wrap tbody tr:nth-child(2n) td {
        background: #f5f5f5;
    }
    table.mpc-wrap tbody tr td.product-name > a {
        text-decoration: none !important;
        font-weight: bold;
    }
    table.mpc-wrap tbody tr td.product-image {
        width: 90px;
    }
    table.mpc-wrap tbody tr:nth-child(2n) td .qty {
        background: #fff;
    }
    table.mpc-wrap img {
        border-radius: 0;
    }
    .mpc-wrap .variation-group {
        margin-bottom: .75em;
    }
    .mpc-wrap .variation-group:last-child {
        margin-bottom: 0;
    }
    .mpc-wrap .variation-group > label {
        display: block;
    }
    .mpc-wrap .variation-group > select {
        width: 100%;
    }
    .mpc-wrap input.input-text.qty.text {
        padding: .15em;
    }
    table.mpc-wrap .product-variation {
        width: 200px;
    }
    .mpc-button {
        text-align: right;
    }
    .mpc-button input.single_add_to_cart_button.wc-forward {
        background: <?php echo $wmc_button_color; ?>;
    }
    .woo-notices{
    }
    .woo-err{
    	padding: 10px 15px;
	    color: #ffffff;
	    border: 1px solid #f00;
	    background: #cc3e3e;
    }

    /* Mobile CSS */
    @media screen and (max-width: 767px) {
        table.mpc-wrap thead, tr.cart_item.simple td.product-variation {
            display: none;
        }
        table.mpc-wrap tbody tr, table.mpc-wrap tbody td {
            display: block;
        }
        table.mpc-wrap tbody tr {
            position: relative;
            border: 1px solid #eee;
            margin-bottom: 1em;
            padding: 1em;
        }
        table.mpc-wrap tbody tr td {
            padding: 0;
        }
        table.mpc-wrap tbody tr td.product-name {
            padding-top: .5em;
        }
        table.mpc-wrap tbody td.product-select {
            position: absolute;
            z-index: 10;
            right: .5em;
            padding: 0 !important;
            top: .5em;
        }
        table.mpc-wrap tbody td.product-quantity {
            margin-top: .5em;
        }
        table.mpc-wrap tbody tr:nth-child(2n) td {
            background: #fdfdfd;
        }
        .mpc-wrap .variation-group {
            margin-bottom: 5px;
        }
    }
</style>