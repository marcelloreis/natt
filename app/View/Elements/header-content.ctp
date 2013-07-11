                <header id="content-header">
                    <div class="left">
                        <?php echo $this->element('shortcuts')?>
                    </div><!-- End .left --> 

                    <div class="right">
                    	<!-- sidebar switch -->
                    	<a href="javascript:void(0);" id="toggle-sidebar" class="button-icon tip-s" title="<?php echo __('Switch Main Menu')?>"><span class="arrow-left-10 plix-10"></span></a>
                        
                        <!-- breadcrumbs -->
                        <?php echo $this->element('breadcrumbs')?>
                        
                        
                        <span class="preloader"></span>
                        
                        <!-- widgets controls -->
                        <?php 
                        $indicators = isset($indicators)?$indicators:array();
                        echo $this->element('controls', $indicators);
                        ?>                        
                    </div><!-- End .right -->                
                
				</header><!-- End #content-header --> 


                <div id="content-inner">


                 <?php echo $this->element('sidebar')?>   

                    <div id="content-main">
                        <div id="content-main-inner">
                            <?php echo $this->element('header-view')?>   
