<div id="e-styleswitcher">
    <div class="e-styleswitcher-inner">
        <div class="e-styleswitcher-arrow"><?php echo $this->Html->image('icons/plix-16/white/arrow-right-16.png', array('alt' => ''))?></div>
        <div class="box">
           <h4>Styles</h4>
           <select id="choose-styling">
               <option value="strangeblue">Strange blue</option>
               <option value="black">Black</option>
               <option value="darkblue">Dark blue</option>
               <option value="lightgrey">Light grey</option>
           </select>
        </div>    
        <div class="box">
           <h4>Layout sizes</h4>                
           <select id="set-layout-size">
               <option value="layout_fluid">fluid</option>
               <option value="layout_768">768</option>
               <option value="layout_960">960</option>
               <option value="layout_1024">1024</option>
               <option value="layout_1200">1200</option>
               <option value="layout_1600">1600</option>
           </select>
        </div> 
        <div class="box">
           <h4>Responsive</h4>                
           <select id="set-layout-responsive">
               <option value="layout_responsive">yes</option>
               <option value="">no</option>
           </select>
        </div>
        <div class="box">
            <h4>Get theme</h4>                
            <select id="get-theme">
               <option value="">no</option>
               <option value="yes">yes</option>
            </select>
        </div>                         
   </div>
</div>