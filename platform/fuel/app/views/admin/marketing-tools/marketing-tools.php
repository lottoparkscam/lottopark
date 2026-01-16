<?php include(APPPATH . 'views/admin/shared/navbar.php') ?>
<div class="container-fluid">
  <div class="col-md-2">
      <?php include(APPPATH . 'views/admin/marketing-tools/menu.php') ?>
  </div>
  <div class="col-md-10">
    <h2>Marketing Tools</h2>
    <p class="help-block">A dedicated section providing tools to enhance your marketing efforts, including banner generation and more.</p>
    <div class="container-fluid container-admin">
      <div class="row">
        <div class="col-md-6">
          <form action="/marketing-tools" method="get" id="marketingToolsForm">
            <div class="form-group">
              <label for="filterBannerType">Banner Type:</label>
              <select name="bannerType" id="filterBannerType" class="form-control" required>
                <option value="1">Jackpot</option>
                <option value="2">Results</option>
              </select>
            </div>
            <div class="form-group">
              <label for="filterLottery">Lottery:</label>
              <select name="lotteryId" id="filterLottery" class="form-control" required>
                <option value="0">Choose lottery...</option>
                  <?php foreach ($lotteries as $lottery): ?>
                    <option value="<?= $lottery['id'] ?>"><?= $lottery['name'] ?></option>
                  <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" id="drawsContainer" style="display: none;">
              <label for="filterDraw">Draw:</label>
              <select name="drawDate" id="filterDraw" class="form-control" required>
                <!-- This part will be generated automatically by JS -->
              </select>
            </div>
            <button type="submit" class="btn btn-large btn-danger">Generate banner</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    const lotteryDraws = <?= json_encode($lotteryDraws) ?>;

    document.addEventListener('DOMContentLoaded', () => {
      const filterLottery = document.getElementById('filterLottery');
      const drawsContainer = document.getElementById('drawsContainer');
      const filterDraw = document.getElementById('filterDraw');
      const filterBannerType = document.getElementById('filterBannerType');

      const addNextDrawOption = () => {
        if (!filterDraw.querySelector('option[value="next_draw"]')) {
          const nextDrawOption = document.createElement('option');
          nextDrawOption.value = 'next_draw';
          nextDrawOption.textContent = 'Next draw';
          filterDraw.insertBefore(nextDrawOption, filterDraw.firstChild);
        }
      };

      const removeNextDrawOption = () => {
        const nextDrawOption = filterDraw.querySelector('option[value="next_draw"]');
        if (nextDrawOption) {
          nextDrawOption.remove();
        }
      };

      filterLottery.addEventListener('change', () => {
        const selectedLotteryId = filterLottery.value;

        if (lotteryDraws[selectedLotteryId] && lotteryDraws[selectedLotteryId][0].length > 0) {
          drawsContainer.style.display = 'block';
          filterDraw.innerHTML = '';

          lotteryDraws[selectedLotteryId][0].forEach((date) => {
            const option = document.createElement('option');
            option.value = date;
            option.textContent = date;
            filterDraw.appendChild(option);
          });

          if (filterBannerType.value === '1') {
            addNextDrawOption();
          }
        } else {
          drawsContainer.style.display = 'none';
        }
      });

      filterBannerType.addEventListener('change', () => {
        if (filterBannerType.value === '1') {
          addNextDrawOption();
        } else {
          removeNextDrawOption();
        }
      });
    });
  </script>
</div>