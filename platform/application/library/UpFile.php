<?php

/**
 * PHP文件上传处理
 */
class UpFile {

    private $data;
    private $pathinfo;

    /**
     * @return array<UploadFile>
     */
    public static function Init(): array {
        foreach ($_FILES as $key => $one) {
            $ret[$key] = new self($one);
        }
        return $ret ?? [];
    }

    /**
     * 遍历上传的文件
     * @param callable $callback
     */
    public static function Each(callable $callback) {
        $files = self::Init();
        array_walk($files, function(UpFile $v, $k) use($callback) {
            call_user_func_array($callback, [$v]);
        });
    }

    private function __construct(array $data) {
        $this->data = $data;
        $this->pathinfo = pathinfo($data['name']);
    }

    /**
     * 文件是否有错误
     */
    public function isError(): bool {
        return $this->getError() > 0 ? true : false;
    }

    /**
     * 返回错误
     */
    public function getError(): int {
        return $this->data['error'] ?? 0;
    }

    /**
     * 文件名称
     * @return string
     */
    public function name(): string {
        return $this->pathinfo['filename'];
    }

    /**
     * 文件类型
     * @return string
     */
    public function type(): string {
        return $this->data['type'];
    }

    /**
     * 文件大小
     * @return int
     */
    public function size(): int {
        return $this->data['size'];
    }

    /**
     * 临时文件未知
     * @return string
     */
    private function tmpName(): string {
        return $this->data['tmp_name'];
    }

    /**
     * 文件后缀名
     * @return string
     */
    public function extension(): string {
        return $this->pathinfo['extension'] ?? '';
    }

    /**
     * 上传的数据
     * @return array
     */
    public function data(): array {
        return $this->data;
    }

    /**
     * 移动文件到指定位置
     * @param string $dir
     * @param mixed $name 当name为null时，以文件原有名字保存；当name=true是，系统将生成一个随机名字；当name=string时，使用用户指定的名称
     * @param bool $extension 文件名是否包含后缀
     * @return string 正确移动则返回存储的文件名称；否则返回false
     */
    public function moveTo(string $dir, $name = null, bool $extension = true): string {
        if (null === $name) {
            $name = $this->name();
        } else if (true == $name) {
            $name = md5($this->name() . uniqid());
        }
        if ($extension) {
            $ext = $this->extension();
            if (!empty($ext)) {
                $name .= '.' . $ext;
            }
        }
        $path = $dir . DIRECTORY_SEPARATOR . $name;
        if ($this->mkdirs($dir)) {
            move_uploaded_file($this->tmpName(), $path);
        } else {
            throw new Exception("dir($dir) access deny");
        }
        return $path;
    }

    /**
     * 创建多层目录
     * @param string $dir
     * @return bool
     */
    protected function mkdirs(string $dir): bool {
        return is_dir($dir) or ( $this->mkdirs(dirname($dir)) and mkdir($dir));
    }

}
