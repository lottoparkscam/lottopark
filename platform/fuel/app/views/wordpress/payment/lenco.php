<div>
  <script src="<?= $viewData['scriptUri'] ?>"></script>
  <script type="text/javascript">
    (function () {
      LencoPay.getPaid({
        ...JSON.parse('<?= json_encode($viewData['params']) ?>'),
        ...{
          onSuccess: function () {
            window.location = '<?= $viewData['verifyUri'] ?>';
          },
          onClose: function () {
            window.location = '<?= $viewData['failureUri'] ?>';
          },
          onConfirmationPending: function () {
            window.location = '<?= $viewData['successUri'] ?>';
          },
        }
      });
    })();
  </script>
</div>
