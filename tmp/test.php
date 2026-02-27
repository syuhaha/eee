<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

use Illuminate\Support\Facades\Log;

use App\Models\TExistingAccount;

class RunPlaywrightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $strToken;
    public string $strTAdd;
    public string $strTitle;
    public string $strCont;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $pistrToken, string $pistrTAdd, string $pistrTitle, string $strCont)
    {
        $this->strToken    = $pistrToken;
        $this->strTAdd     = $pistrTAdd;
        $this->strTitle  = $pistrTitle;
        $this->strCont = $strCont;
    }

    /**
     * Execute the job: 调用 Python 脚本，传参并接收返回结果。
     *
     * @return void
     */
    public function handle()
    {
        $strAccount = '';
        $strPwd = '';

        $rcdTEA = TExistingAccount::where('status', '>=', 1)
                                                              ->where('account', 'like', '+%')
                                                              ->orderByDesc('id')     // 或 ->latest('id')
                                                              ->first(['account', 'password']);
        if ($rcdTEA) {
            $strAccount  = ltrim($rcdTEA->account, '+');
            $strPwd = $rcdTEA->password;
        }

        // 脚本路径（Laravel 根目录为 /www/wwwroot/eml 时，即 /www/wwwroot/eml/scripts/run_playwright.py）
        $script = base_path('scripts/run_playwright.py');

        $process = new Process([
            'python3',
            $script,
            $this->strTAdd,
            $this->strTitle,
            $this->strCont,
            $strAccount,
            $strPwd
        ]);

        // 单次执行 >30 秒，调大或关闭超时
//        $process->setTimeout(300); // 或 null 表示不限制
        $process->setTimeout(null); // 或 null 表示不限制
        $process->run();

        if (! $process->isSuccessful()) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            throw new \RuntimeException('Python script failed: ' . $error);
        }

	   $output = $process->getOutput();
	   $result = json_decode($output, true);


	   if (json_last_error() !== JSON_ERROR_NONE) {
	      throw new \RuntimeException('Python script returned invalid JSON: ' . $output);
	   }


//        if (json_last_error() !== JSON_ERROR_NONE) {
//            throw new \RuntimeException('Python script returned invalid JSON: ' . $output);
//        }

        // $result 为 PHP 数组，例如：['status' => 'ok', 'screenshots' => [...], ...]
        $strNewAcc  = $result[0] ?? '';
        $strNewPwd = $result[1] ?? '';


// Log::info('RunPlaywrightJob received 333444', ['output' => [$strNewAcc, $strNewPwd]]);

        if ('' != $strNewAcc) {

            if (ltrim($strNewAcc, '+') != $strAccount) {
                TExistingAccount::create([
	            'account'   	=> $strNewAcc,
	            'password'       => $strNewPwd 
	         ]);
            }

            DB::table('t_invitation_code_mng')
              ->where('invitation_code', $this->strToken)
              ->decrement('operate_times', 1, [
                'user_ip' => DB::raw("CONCAT(COALESCE(user_ip, ''), ';', '" . $suffix . "')")
             ]);
        }
    }
}
