# CS5281 在线测验系统

这是一个为 `CS5281 Internet Application Development` 课程作业实现的在线测验系统。项目采用 `PHP + HTML + CSS + JavaScript` 的经典网页应用结构：PHP 负责登录、会话、文件读写、自动评分和 CSV 导入导出；HTML 负责页面结构；CSS 负责样式；JavaScript 负责前端校验、倒计时和 AJAX 统计加载。

## 项目功能

- Instructor / Student 双角色登录
- 基于 PHP Session 的登录状态管理
- 学生账号 CSV 批量导入
- 学生分组创建、修改、删除、分配
- MCQ 题库管理
- Quiz 创建、发布、关闭
- 按 student group 分配 quiz
- 学生在线答题、倒计时、前端校验
- 自动评分，每个学生每个 quiz 仅允许提交一次
- 学生成绩详情与历史记录
- Instructor 结果统计页面
- AJAX 异步加载 quiz 统计
- 成绩 CSV 导出

## 技术栈

- PHP 8.5+
- HTML5
- CSS3
- 原生 JavaScript
- JSON 文件存储
- CSV 导入 / 导出

## 目录结构

```text
public/               浏览器入口页面与静态资源
  assets/             CSS 和 JavaScript
  instructor/         教师端页面
  student/            学生端页面
src/                  共享 PHP 业务逻辑
data/                 JSON 数据文件与导出 CSV
tests/                PHP CLI 测试
docs/                 requirements、设计文档、实现计划等
```

## 环境依赖

运行本项目至少需要：

1. PHP 8.5 或更高版本
2. 可以运行 PHP 内置开发服务器的终端环境
3. Git（用于克隆和更新项目）

### macOS 使用 Homebrew 安装 PHP

```bash
brew install php
php -v
```

如果 `php -v` 能正常输出版本号，说明环境已经准备好。

## 初始化与启动项目

先克隆项目：

```bash
git clone https://github.com/bigyong-996/cs5281-group-online-quiz.git
cd cs5281-group-online-quiz
```

然后在项目根目录启动 PHP 内置服务器：

```bash
php -S 127.0.0.1:8000 -t public
```

启动后在浏览器中打开：

```text
http://127.0.0.1:8000
```

## 默认账号

当 `data/users.json` 为空时，系统会自动生成一个默认 instructor 账号。

- 用户名：`instructor`
- 密码：`instructor123`

这个账号会在第一次访问登录页时自动写入。

## 学生 CSV 导入格式

教师导入学生时，CSV 文件表头必须是：

```csv
username,display_name,initial_password
alice,Alice Chan,alice123
bob,Bob Lee,bob123
```

你可以直接参考示例文件：

`tests/fixtures/students.csv`

## 推荐操作流程

建议按下面顺序演示或测试：

1. Instructor 登录
2. 导入学生 CSV
3. 创建 student group
4. 将学生分配到 group
5. 新增 MCQ 题目
6. 创建 quiz 并发布
7. 使用学生账号登录
8. 进入 quiz 答题并提交
9. 查看学生历史成绩
10. 回到教师账号查看结果
11. 通过 AJAX 加载统计并导出 CSV

## 运行测试

运行全部 PHP CLI 测试：

```bash
for test in tests/*_test.php; do php "$test" || exit 1; done
```

运行 PHP 语法检查：

```bash
find src public tests -name '*.php' -print -exec php -l {} \;
```

## 数据存储说明

本项目有意采用文件存储，而不是数据库，这和作业要求比较贴近，也方便快速完成与演示。

主要数据文件包括：

- `data/users.json`
- `data/groups.json`
- `data/questions.json`
- `data/quizzes.json`
- `data/submissions.json`
- `data/export/`

## 补充说明

- 导出的 CSV 文件默认不会被 Git 跟踪。
- 本项目里的 `.php` 页面不是“只有 PHP”，而是 PHP 负责服务端处理后输出 HTML 页面。
- CSS 在 `public/assets/styles.css`。
- JavaScript 在 `public/assets/app.js`。
- JavaScript 主要用于答题页校验、倒计时和 instructor 统计页的 AJAX 异步加载。
