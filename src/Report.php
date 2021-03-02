<?php

namespace careyshop;

use think\Route;
use think\Service;

class Report extends Service
{
    /**
     * 获取本地构建路径
     * @return array
     */
    private function getLocalReportFile()
    {
        $dir = 'static';
        $file = false;
        $path = public_path() . $dir;
        $dirList = scandir($path);

        foreach ($dirList as $value) {
            if ('.' === $value || '..' === $value) {
                continue;
            }

            if (!is_dir($path . DIRECTORY_SEPARATOR . $value)) {
                continue;
            }

            $filePath = $path . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR . 'report.html';
            if (file_exists($filePath)) {
                $file = $filePath;
                $dir .= '/' . $value;
                break;
            }
        }

        return [$file, $dir];
    }

    /**
     * 获取远程构建路径
     * @param string $url URL
     * @return array
     */
    private function getUrlReportFile($url)
    {
        $pathInfo = pathinfo($url);
        return [
            $url,
            is_array($pathInfo) ? $pathInfo['dirname'] : '',
        ];
    }

    /**
     * 获取构建文件内容
     * @param string $file 访问地址
     * @param string $dir  文件路径
     * @return string
     */
    private function getContent($file, $dir)
    {
        $content = file_get_contents($file);
        if (!is_string($content)) {
            return '读取构建文件失败';
        }

        if (preg_match('/window.chartData =(.+?);/', $content, $match)) {
            $script = '';
            $chartData = @json_decode($match[1], true);

            if (is_array($chartData)) {
                foreach ($chartData as $value) {
                    if (!stripos($value['label'], 'app.')) {
                        $script .= "<script src='${dir}/${value['label']}'></script>";
                    }
                }

                $content .= $script;
            }
        }

        return $content;
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            $route->get('report', function () {
                $url = urldecode(request()->get('url'));
                [$file, $dir] = $url ? $this->getUrlReportFile($url) : $this->getLocalReportFile();

                if (!$file) {
                    return '构建文件不存在';
                }

                return $this->getContent($file, $dir);
            });
        });
    }
}
