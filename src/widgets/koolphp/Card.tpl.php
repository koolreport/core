<?php
use \koolreport\core\Utility;

$cardStyle = Utility::get($this->cssStyle, "card");
$valueStyle = Utility::get($this->cssStyle, "value");
$indicatorStyle = Utility::get($this->cssStyle, "indicator");
$titleStyle = Utility::get($this->cssStyle, "title");
$negativeStyle = Utility::get($this->cssStyle, "negative");
$positiveStyle = Utility::get($this->cssStyle, "positive");

$cardClass = Utility::get($this->cssClass, "card");
$valueClass = Utility::get($this->cssClass, "value");
$indicatorClass = Utility::get($this->cssClass, "indicator");
$titleClass = Utility::get($this->cssClass, "title");
$upIconClass = Utility::get($this->cssClass, "upIcon", "fa fa-caret-up");
$downIconClass = Utility::get($this->cssClass, "downIcon", "fa fa-caret-down");

if ($this->baseValue!==null) {
    $indicatorValue = $this->calculateIndicator($this->value, $this->baseValue, $this->indicator);
    $indicatorStyle .= (($indicatorStyle)?";":"").
                    (($indicatorValue<0)?$negativeStyle:$positiveStyle);
    $indicatorTitle = str_replace("{baseValue}", (string)$this->formatValue($this->baseValue, $this->valueFormat), (string)$this->indicatorTitle);
    $indicatorTitle = str_replace("{value}", (string)$this->formatValue($this->value, $this->valueFormat), (string)$indicatorTitle);
}

$href = $this->getHref();
if ($href) {
    $cardStyle ="cursor:pointer;$cardStyle";
}
?>
<div id="<?php echo $this->name; ?>" <?php echo ($href)?$href:""; ?>class="koolphp-card card panel<?php echo ($cardClass)?" $cardClass":""; ?>"<?php echo($cardStyle)?" style='$cardStyle'":""; ?>>
    <div class="panel-body card-body">
        <?php if ($this->baseValue!==null) :?>
            <div class="card-indicator<?php echo ($indicatorClass)?" $indicatorClass":""; ?><?php echo ($indicatorValue<0)?" value-negative":" value-positive"; ?>"<?php echo($indicatorStyle)?" style='$indicatorStyle'":""; ?>>
                <span title="<?php echo $indicatorTitle; ?>">
                    <?php echo $this->formatValue($indicatorValue, $this->indicatorFormat); ?>
                    <i class='<?php echo($indicatorValue<0)?$downIconClass:$upIconClass; ?>'></i>
                </span>
            </div>
        <?php endif ?>
        <div class="card-value<?php echo ($valueClass)?" $valueClass":""; ?>"<?php echo($valueStyle)?" style='$valueStyle'":""; ?>>
            <?php echo $this->formatValue($this->value, $this->valueFormat); ?>
        </div>
        <div class="card-title<?php echo ($titleClass)?" $titleClass":""; ?>"<?php echo($titleStyle)?" style='$titleStyle'":""; ?>>
            <?php echo $this->title; ?>
        </div>
    </div>
</div>
<script type="text/javascript">
KoolReport.widget.init(<?php echo json_encode($this->getResources()); ?>);
</script>