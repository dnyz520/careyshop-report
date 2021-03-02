<?php

namespace careyshop;

use think\Route;
use think\Service;

const DS = DIRECTORY_SEPARATOR;

class Report extends Service
{
    /**
     * 获取本地构建文件路径
     * @return false|string
     */
    private function getLocalReportFile()
    {
        $path = public_path() . 'static';
        $dirs = scandir($path);

        foreach ($dirs as $value) {
            if ('.' === $value || '..' === $value) {
                continue;
            }

            if (!is_dir($path . DS . $value)) {
                continue;
            }

            $filePath = $path . DS . $value . DS . 'report.html';
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        return false;
    }

    private function getContent($path)
    {
        $content = file_get_contents($path);
        if (false === $content) {
            return '读取构建文件失败';
        }

        if (preg_match('/window.chartData =(.+?);/', $content, $match)) {
            $chartData = @json_decode($match[1], true);
            if (is_array($chartData)) {
                foreach ($chartData as $value) {
                    $content .= "<script src='${value['label']}'></script>";
                }
            }
        }

//        dump($content);exit();
//        return $content;
        print_r($path);exit();
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            $route->get('report', function () {
                $file = $this->getLocalReportFile();
                if (false === $file) {
                    return '构建文件不存在';
                }

                return $this->getContent($file);
            });
        });
    }
}
