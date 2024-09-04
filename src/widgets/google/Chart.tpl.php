<?php
/**
 * This file is template file of Google Chart 
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

use \koolreport\core\Utility;
?>
<div id="<?php echo $this->name; ?>" style="<?php if ($this->width) echo "width:".$this->width.";"; ?><?php if ($this->height) echo "height:".$this->height.";"; ?>"></div>

<?php if ($this->jsonRender) { ?>
    <div id="<?php echo $this->name; ?>_json" style="display: none">
        <json_script>
            <?php
            echo json_encode([
                [
                    "function" => "KoolReport.widget.init",
                    "arguments" => [
                        $this->getResources(),
                        [
                            "function" => "{anonymous}",
                            "arguments" => [],
                            "json" => [
                                [
                                    "function" => "KoolReport.widget.runGoogleChart",
                                    "arguments" => [
                                        [
                                            'chartType' => $chartType,
                                            'name' => $this->name,
                                            'cKeys' => $cKeys,
                                            'data' => $data,
                                            'options' => $options,
                                            'loader' => $loader,
                                            'pointerOnHover' => $this->pointerOnHover,
                                            'clientEvents' => $this->clientEvents,
                                            'onReady' => $this->onReady,
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]);
            ?>
        </json_script>
        <script>
            console.log('GoogleChart json render');
            KoolReport.helper.executeJsonScript("#" + "<?php echo $this->name; ?>_json");
        </script>
    </div>
<?php } else { ?>
    <script type="text/javascript">
        KoolReport.widget.init(<?php echo json_encode($this->getResources()); ?>,function(){
            <?php echo $this->name; ?> = new KoolReport.google.chart("<?php echo $chartType; ?>","<?php echo $this->name; ?>",<?php echo json_encode($cKeys);?>,<?php echo Utility::jsonEncode($data);?>,<?php echo Utility::jsonEncode($options);?>,<?php echo json_encode($loader); ?>);
            <?php
            if ($this->pointerOnHover) {
                echo "$this->name.pointerOnHover=true;";    
            }
            ?>
            <?php
            foreach ($this->clientEvents as $event=>$function) {
            ?>
                <?php echo $this->name; ?>.registerEvent("<?php echo $event; ?>",<?php echo $function; ?>);
            <?php
            }
            ?>
            <?php $this->clientSideReady(); ?>
        });
    </script>
<?php } ?>