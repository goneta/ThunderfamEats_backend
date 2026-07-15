<!-- <div class="dropdown language-bar d-inline">	      
    <a class="dropdown-toggle text-truncate" href="javascript:;" 
    role="button" id="currencySelection" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    USD
    </a>		    
    <div class="dropdown-menu" aria-labelledby="currencySelection">				    
        <a class="dropdown-item active" >
         <div class="d-flex align-items-center">            
            <div class="text-truncate">American USD ($)</div>
         </div>
        </a>
        <a class="dropdown-item" >
         <div class="d-flex align-items-center">            
            <div class="text-truncate">South Korean Won (KRW)</div>
         </div>
        </a>
    </div>		   
</div> -->
<component-currency-selection
@after-selectcurrency="afterSelectcurrency"
>
</component-currency-selection>