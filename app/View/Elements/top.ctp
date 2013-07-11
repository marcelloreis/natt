<?php 
$this->assign('themes', $this->element('themes'));
?>


<div id="container">
	<!-- MAIN HEADER -->

	<header id="header">
		<div id="header-border">
			<div id="header-inner">

				<div class="left">
					<?php echo $this->Html->link(TITLE_APP, array('controller' => 'users', 'action' => 'dashboard', 'plugin' => false), array('id' => 'logo'))?>
				</div><!-- End .left -->

				<div class="right">
					<!-- eMenu -->
					<nav>
						<ul class="e-splitmenu" id="header-menu">
							<li class="e-menu-profile">
                                <?php echo $this->element('menu-top-user')?>
                            </li>
                        </ul>
                    </nav>
                </div><!-- End .right --> 

            </div><!-- End #header-border --> 
        </div><!-- End #header-inner -->  

    </header><!-- End #header -->


    <!-- CONTENT -->
<div id="content">
        <div id="content-border">

    <!-- CONTENT HEADER -->
    <?php echo $this->element('header-content')?>

	             	











