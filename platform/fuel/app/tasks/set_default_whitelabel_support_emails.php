<?php

namespace Fuel\Tasks;

use Container;
use Repositories\WhitelabelRepository;
use Models\Whitelabel;

/** This task can be also used after adding new wl. It will skip wl if it has supportEmail or paymentEmail */
final class Set_Default_Whitelabel_Support_Emails
{
    public function run()
    {
        /** @var WhitelabelRepository $whitelabelDomains */
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabelDomains = $whitelabelRepository->getAllWhitelabelDomains();

        foreach ($whitelabelDomains as $whitelabelDomain) {
            $entry = Whitelabel::query()
            ->where('domain', '=', $whitelabelDomain)
            ->get_one();

            if(!empty($entry->support_email) || !empty($entry->payment_email)){
                echo "$whitelabelDomain has set support emails before. Skipping.. \n";
                continue;
            }

            $entry->support_email = 'support@' . $whitelabelDomain;
            $entry->payment_email = 'payments@' . $whitelabelDomain;
            $resultMessage = $entry->save() ? 'success' : 'fail';
            echo "Updating $whitelabelDomain contact emails with $resultMessage \n";
        }
    }
}
