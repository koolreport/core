<?php
/**
 * This file is the view of table widget 
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

    use \koolreport\core\Utility;
    use \koolreport\widgets\koolphp\Table;
    $tableCss = Utility::get($this->cssClass, "table");
    $trClass = Utility::get($this->cssClass, "tr");
    $tdClass = Utility::get($this->cssClass, "td");
    $thClass = Utility::get($this->cssClass, "th");
    $tfClass = Utility::get($this->cssClass, "tf");
    $groups = $this->generateGroups($meta);
?>
<?php
    $footer_content = "";
    if ($this->showFooter) {
    ob_start();
?>
    <tfoot <?php echo ($this->showFooter==="top")?"style='display:table-row-group'":""; ?>>
        <tr>
        <?php
        foreach ($showColumnKeys as $cKey) {
            $cssStyle = Utility::get($meta["columns"][$cKey], "cssStyle", null);
            $tfStyle = is_string($cssStyle)?$cssStyle:Utility::get($cssStyle, "tf");
        ?>
            <td <?php if($tfClass){echo " class='".((gettype($tfClass)=="string")?$tfClass:$tfClass($cKey))."'";} ?> <?php echo ($tfStyle)?"style='$tfStyle'":""; ?> >
                <?php 
                    $footerValue = "";
                    $method = Utility::get($meta["columns"][$cKey], "footer");
                    if (gettype($method)!="string" && is_callable($method)) {
                        $footerValue = $method($this->dataStore);
                    } else if (gettype($method)=="string") { 
                        $method = strtolower($method);
                        if (in_array($method, array("count","sum","avg","min","max","mode")) ) {
                            $footerValue = Table::formatValue($this->dataStore->$method($cKey), $meta["columns"][$cKey], $cKey);
                        }
                    }   
                    $footerText = Utility::get($meta["columns"][$cKey],"footerText");
                    if ($footerText!==null) {
                        echo str_replace("@value", (string)$footerValue, (string)$footerText);
                    } else {
                        echo $footerValue;
                    }
                ?>
            </td>	
        <?php	
        }
        ?>
        </tr>
    </tfoot>
<?php	
    $footer_content = ob_get_clean();
}    
?>
<div class="koolphp-table <?php echo $this->responsive?"table-responsive":"";?>" id="<?php echo $this->name; ?>">
    <table<?php echo ($tableCss!==null)?" class='table $tableCss'":" class='table'"; ?>>
        <?php
        if ($this->showHeader) {
        ?>
        <thead>
            <?php
            foreach ($this->headers as $header) {
            ?>
            <tr>
                <?php
                foreach ($header as $hName=>$hValue) {
                    ?>
                    <th<?php
                    foreach ($hValue as $k=>$v) {
                        if ($k!="label") {
                            echo " $k='$v'";
                        }
                    }
                    ?>><?php echo Utility::get($hValue, "label", $hName); ?></th>
                    <?php
                }
                ?>
            </tr>
            <?php	
            }
            ?>
            <tr>
            <?php 
            foreach ($showColumnKeys as $cKey) {
                $label = Utility::get($meta["columns"][$cKey], "label", $cKey);
                $cssStyle = Utility::get($meta["columns"][$cKey], "cssStyle", null);
                $thStyle = is_string($cssStyle)?$cssStyle:Utility::get($cssStyle, "th");
                $class = "";
                if ($thClass) {
                    $class = (gettype($thClass)=="string")?$thClass:$thClass($cKey);
                }
                echo "<th".(($thStyle)?" style='$thStyle'":"").(($class!="")?" class='$class'":"").">$label</th>";
            }
            ?>
            </tr>
        </thead>
        <?php	
        }
        ?>
        <?php echo $this->showFooter==="top"?$footer_content:""; ?>
        <tbody>
            <?php
            foreach ($this->dataStore as $i=>$row) {
                $rowStyle = "";
                if($this->paging)
                {
                    if($i<$this->paging["pageIndex"]*$this->paging["pageSize"] || $i>=($this->paging["pageIndex"]+1)*$this->paging["pageSize"])
                    {
                        $rowStyle.="display:none;";
                    }
                }
                $this->renderRowGroup($groups, $i, count($showColumnKeys));
            ?>
            <tr ri='<?php echo $i; ?>'<?php echo ($rowStyle!="")?" style='$rowStyle'":""; ?><?php if($trClass){echo " class='".((gettype($trClass)=="string")?$trClass:$trClass($row))."'";} ?>>
                <?php
                foreach ($showColumnKeys as $cKey) {
                    $cssStyle = Utility::get($meta["columns"][$cKey], "cssStyle", null);
                    $tdStyle = is_string($cssStyle)?$cssStyle:Utility::get($cssStyle, "td");
                    $value = "";
                    if($cKey==="#")
                    {
                        $value = $i+$meta["columns"][$cKey]["start"];
                    } else if (strpos($cKey,"__custom")===0) {
                        $value = $meta["columns"][$cKey]["value"]($row);
                    } else {
                        $value = Utility::get($row, $cKey, $this->emptyValue);
                    }
                    ?>
                        <td rv="<?php echo (in_array(gettype($value),array("array")))?"":htmlspecialchars($value);?>" <?php echo ($tdStyle)?"style='$tdStyle'":""; ?> <?php if($tdClass){echo " class='".((gettype($tdClass)=="string")?$tdClass:$tdClass($row,$cKey))."'";} ?>>
                            <?php echo Table::formatValue($value, $meta["columns"][$cKey], $row, $cKey);?>
                        </td>
                    <?php
                }
                ?>
            </tr>
            <?php
            }
            ?>
            <?php
            if ($this->dataStore->countData()>0) {
                $this->renderRowGroup($groups, $i+1, count($showColumnKeys));
            } else {
            ?>
                <tr><td colspan="<?php echo count($showColumnKeys); ?>" align="center"><?php echo $this->translate("No data available in table"); ?></td></tr>
            <?php	
            }
            ?>
        </tbody>
        <?php echo ($this->showFooter && $this->showFooter!=="top")?$footer_content:""; ?>
    </table>
    <?php
    if ($this->paging) {
    ?>
    <div style='text-align:<?php echo $this->paging["align"]; ?>'>
        <nav></nav>
    </div>
    <?php	
    }
    ?>
</div>
<script type="text/javascript">
KoolReport.widget.init(<?php echo json_encode($this->getResources()); ?>,function(){
    <?php echo $this->name; ?> = new KoolReport.koolphp.table('<?php echo $this->name; ?>',<?php echo json_encode(array(
    "cKeys"=>$showColumnKeys,
    "removeDuplicate"=>$this->removeDuplicate,
    "paging"=>$this->paging,
    )); ?>);
    <?php
    if ($this->clientEvents) {
        foreach ($this->clientEvents as $eventName=>$function) {
        ?>
            <?php echo $this->name; ?>.on("<?php echo $eventName; ?>",<?php echo $function; ?>);
        <?php	
        }
    }
    ?>
    <?php $this->clientSideReady(); ?>
});
</script>