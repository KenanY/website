<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="no-contain">
  <div id="page-wrap">
    <?php include Tpl::file('seg/top.php') ?>
    <?php include Tpl::file('seg/admin.nav.php') ?>

    <?php if(!empty($model->success)): ?>
    <section class="container">
      <div class="alert alert-info" style="margin-bottom:0;">
        <strong>Success!</strong>
        <?=Tpl::out($model->success)?>
      </div>
    </section>
    <?php endif; ?>

    <section class="container">
      <h3>Subscription <small>(<a href="/admin/user/<?=Tpl::out($model->user['userId'])?>/edit"><?=Tpl::out($model->user['username'])?></a>)</small></h3>
      <div class="content content-dark clearfix">

        <?php
        $url = '/admin/user/'. urlencode($model->user['userId']) .'/subscription/save';
        if(!empty($model->subscription) && !empty($model->subscription['subscriptionId'])){
          $url = '/admin/user/'. urlencode($model->user['userId']) .'/subscription/'. urlencode($model->subscription['subscriptionId']) . '/save';
        }
        ?>

        <form action="<?=$url?>" method="post">

          <div class="ds-block">
            <div class="form-group">
              <label>Type:</label>
              <select name="subscriptionType" class="form-control">
                <option value="">Select a subscription type</option>
                <option value="">&nbsp;</option>
                <?php foreach($model->subscriptions as $sub): ?>
                  <option value="<?=Tpl::out($sub['id'])?>" <?=(strcasecmp($model->subscription['subscriptionType'], $sub['id']) === 0) ? 'selected="selected"':''?>><?=Tpl::out($sub['itemLabel'])?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="control-label" for="inputGifter">Gifter</label>
              <div class="controls">
                <input type="text" class="form-control" name="gifter" id="inputGifter" value="<?=Tpl::out($model->subscription['gifter'])?>" placeholder="Gifter user id">
              </div>
            </div>

            <div class="form-group">
              <label>Status:</label>
              <select name="status" class="form-control">
                <option value="<?=SubscriptionStatus::ACTIVE?>" <?=(strcasecmp($model->subscription['status'], SubscriptionStatus::ACTIVE) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::ACTIVE?></option>
                <option value="<?=SubscriptionStatus::CANCELLED?>" <?=(strcasecmp($model->subscription['status'], SubscriptionStatus::CANCELLED) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::CANCELLED?></option>
                <option value="<?=SubscriptionStatus::EXPIRED?>" <?=(strcasecmp($model->subscription['status'], SubscriptionStatus::EXPIRED) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::EXPIRED?></option>
                <option value="<?=SubscriptionStatus::PENDING?>" <?=(strcasecmp($model->subscription['status'], SubscriptionStatus::PENDING) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::PENDING?></option>
              </select>
            </div>

            <div class="form-group">
              <label class="control-label" for="inputStarttimestamp">Start</label>
              <div class="controls">
                <input type="text" class="form-control" name="createdDate" id="inputStarttimestamp" value="<?=Tpl::out($model->subscription['createdDate'])?>" placeholder="Y-m-d H:i:s">
                <span class="help-block">time specificed in UCT</span>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label" for="inputEndtimestamp">End</label>
              <div class="controls">
                <input type="text" class="form-control" name="endDate" id="inputEndtimestamp" value="<?=Tpl::out($model->subscription['endDate'])?>" placeholder="Y-m-d H:i:s">
                <span class="help-block">time specificed in UCT</span>
              </div>
            </div>

            <div class="form-group">
              <label>Recurring:</label>
              <strong><?=($model->subscription['recurring'] == '1') ? 'Yes':'No'?></strong>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="/admin/user/<?=Tpl::out($model->user['userId'])?>/edit" class="btn">Cancel</a>
          </div>

        </form>
      </div>
    </section>

    <?php if(!empty($model->subscription['paymentProfileId'])): ?>
    <section class="container">
      <h3>Payment Status</h3>
      <div class="content content-dark clearfix">
        <table class="grid">
          <thead>
          <tr>
            <td>Status</td>
            <td>Payment Profile Id</td>
            <td>Start Payment Date</td>
            <td>Next Payment Date</td>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td><?=Tpl::out($model->subscription['paymentStatus'])?></td>
            <td><?=Tpl::out($model->subscription['paymentProfileId'])?></td>
            <td><?=Tpl::moment(Date::getDateTime($model->subscription['billingStartDate']), Date::STRING_FORMAT_YEAR)?></td>
            <td><?=Tpl::moment(Date::getDateTime($model->subscription['billingNextDate']), Date::STRING_FORMAT_YEAR)?></td>
          </tr>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

    <?php if(!empty($model->payments)): ?>
    <section class="container">
      <h3>Payments</h3>
      <div class="content content-dark clearfix">
        <table class="grid">
          <thead>
            <tr>
              <td>Id</td>
              <td>Amount</td>
              <td>Created</td>
              <td>Transaction Id</td>
              <td>Transaction Type</td>
              <td>Payment Type</td>
              <td>Payer Id</td>
              <td>Status</td>
            </tr>
          </thead>
          <tbody>
          <?php foreach($model->payments as $payment): ?>
            <tr>
              <td><?=Tpl::out($payment['paymentId'])?></td>
              <td><?=Tpl::out($payment['amount'])?> <?=Tpl::out($payment['currency'])?></td>
              <td><?=Tpl::moment(Date::getDateTime($payment['paymentDate']), Date::STRING_FORMAT_YEAR)?></td>
              <td><?=Tpl::out($payment['transactionId'])?></td>
              <td><?=Tpl::out($payment['transactionType'])?></td>
              <td><?=Tpl::out($payment['paymentType'])?></td>
              <td><?=Tpl::out($payment['payerId'])?></td>
              <td><?=Tpl::out($payment['paymentStatus'])?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

  </div>

  <?php include Tpl::file('seg/foot.php') ?>
  <?php include Tpl::file('seg/commonbottom.php') ?>
  
  <script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
  
</body>
</html>