# Online Quiz System Demo Video Guide

这份文档用于指导小组拍摄作业要求中的 demo video。根据课程 requirement：

- 需要上传 **10 分钟以内** 的演示视频
- 只需要展示 **重要功能**
- 不需要为了证明系统能处理错误输入，而故意现场反复演示错误操作；可以在视频里口头说明，老师会看源码
- requirement 里明确建议：**在正式录制前先准备一个 checklist**

因此，最适合这次项目的拍法不是“边想边录”，而是提前准备一条清晰的操作路径，在 `6 到 8 分钟` 内完成主要功能展示。

## 一、推荐视频结构

建议把视频分成 5 段：

1. 项目介绍
2. Instructor 端演示
3. Student 端演示
4. Instructor 结果与统计演示
5. 收尾总结

这样结构最清楚，也最符合老师“展示重要功能”的要求。

## 二、推荐时长分配

总时长建议控制在 **6 到 8 分钟**，不要卡到 10 分钟上限。

### 1. 开场介绍：30 秒到 45 秒

讲清楚：

- 项目名称：Online Quiz System
- 使用技术：HTML、CSS、JavaScript、PHP
- 两种角色：Instructor、Student
- 系统核心流程：老师建题和发布 quiz，学生答题，系统自动评分并展示结果

示例开场词：

> 大家好，我们这组的项目是 Online Quiz System。这个系统使用 HTML、CSS、JavaScript 和 PHP 开发，支持 Instructor 和 Student 两种角色。Instructor 可以导入学生、管理分组、建立题库和发布 quiz；Student 可以登录后参加被分配的 quiz，系统会自动评分并保存历史记录。

## 三、正式演示顺序

### 2. Instructor 端演示：约 2 分钟到 3 分钟

建议按下面顺序展示：

1. Instructor 登录
2. 展示 dashboard
3. 打开学生导入页面，说明支持 CSV 导入
4. 导入学生账号
5. 打开 group management 页面，创建 group，并分配学生
6. 打开 question bank，新增 1 到 2 道 MCQ
7. 打开 quiz management，创建一个 quiz，设置时间限制、分配 group、发布 quiz

这里不建议现场录很多重复输入。可以提前准备好一个简单 CSV，并且把讲解重点放在：

- 系统支持双角色
- 支持 CSV 导入
- 支持 group 分配
- 支持 MCQ 题库
- 支持 quiz 发布

### 3. Student 端演示：约 1 分钟到 2 分钟

建议顺序：

1. 退出 Instructor
2. 使用导入的 student 账号登录
3. 展示 student dashboard，只能看到分配给自己 group 的 quiz
4. 进入 quiz 页面
5. 展示倒计时
6. 作答并提交

这里重点口头说明：

- 前端有 JavaScript 校验
- quiz 有时间限制
- 每个学生对同一个 quiz 只能提交一次

如果你们不想在视频里专门演示“没选答案时不能提交”，可以直接说一句：

> 系统在前端有 JavaScript validation，如果题目未完成，提交时会提示用户先完成作答。

这正好符合 requirement 的意思。

### 4. 成绩与统计演示：约 1 分钟到 1 分 30 秒

建议顺序：

1. 提交后展示学生成绩页
2. 展示正确 / 错误答案对比
3. 展示 student history 页面
4. 切回 instructor 账号
5. 打开 results 页面
6. 选择 quiz，展示 AJAX 异步加载统计结果
7. 展示 CSV 导出

这一段是整个系统的“闭环证明”，非常重要。老师看到这部分，基本就能确认：

- quiz 真的发布成功了
- student 真的提交成功了
- 系统真的自动评分了
- instructor 真的能看到结果和统计

### 5. 收尾总结：20 秒到 30 秒

最后建议简单总结一下实现了哪些 requirement 相关能力，例如：

- 使用 PHP 实现后端逻辑和 session
- 使用 JavaScript 实现 validation、timer、AJAX
- 使用 HTML / CSS 构建页面
- 使用 CSV 和 JSON 文件实现数据管理

示例结尾词：

> 以上就是我们 Online Quiz System 的演示。这个系统实现了课程作业要求中的前后端功能，包括 HTML / CSS 页面、JavaScript validation 和 AJAX，以及 PHP session、文件存储、CSV 导入导出和自动评分。谢谢老师观看。

## 四、推荐录制 checklist

正式录之前，建议照着下面确认一遍：

- Instructor 默认账号可以登录
- 学生 CSV 文件准备好
- 学生账号已经知道用户名和密码
- 至少有一个 group
- 至少有两道 MCQ
- 至少有一个 published quiz
- quiz 已经分配给某个 student group
- 学生登录后能看到 quiz
- 提交后能看到 result page
- Instructor results 页面能显示统计
- CSV export 能正常下载
- 浏览器页面整洁，没有多余报错

## 五、建议的录制方式

### 方式 A：单人旁白录屏

这是最推荐的做法。

优点：

- 最省时间
- 节奏最好控制
- requirement 也明确说不需要所有成员轮流讲话

### 方式 B：无真人出镜，只录屏配旁白

也完全没问题。requirement 已经明确说：

- 不需要 face-to-face demo
- 不需要在视频里露脸

所以最实用的方案就是：

- 一个人操作
- 一个人或同一个人配旁白
- 全程录屏

## 六、拍摄建议

- 尽量提前把浏览器标签页整理干净
- 不要在视频里长时间等待输入
- 演示数据提前准备好
- 不要把时间浪费在重复操作上
- 页面切换前先口头说下一步要展示什么
- 如果某个功能不方便现场演示错误路径，就直接口头说明

## 七、一版可直接照着录的脚本

下面是一版可以直接参考的简化脚本：

### 开场

> 大家好，我们这组的项目是 Online Quiz System。系统使用 HTML、CSS、JavaScript 和 PHP 开发，支持 Instructor 和 Student 两种角色。下面我们演示系统的主要功能。

### Instructor 部分

> 首先我们使用 Instructor 账号登录。进入 dashboard 后，可以看到系统概览。  
> 接着我们进入学生导入页面，通过 CSV 导入学生账号。  
> 然后我们到 group management 页面创建 student group，并把学生分配到对应 group。  
> 接下来在 question bank 中新增多项选择题。  
> 然后在 quiz management 页面创建一个新的 quiz，设置时间限制，选择题目，并将 quiz 分配给某个 group，最后发布。

### Student 部分

> 现在我们退出 Instructor，使用 Student 账号登录。  
> 在 student dashboard 中，可以看到这个学生所属 group 被分配到的 quiz。  
> 进入 quiz 后，页面会显示倒计时。系统也有 JavaScript validation，防止未完成答题直接提交。  
> 现在我们完成作答并提交，系统会自动评分。

### Result 部分

> 提交后，系统展示本次成绩以及每道题的正确与错误情况。  
> 在 history 页面中，学生还可以查看以往提交记录。  
> 之后我们回到 Instructor 账号，在 results 页面查看 quiz 的统计结果。  
> 这里的统计是通过 AJAX 异步加载的。  
> 最后我们还可以把 quiz 结果导出成 CSV 文件。

### 结尾

> 以上就是我们系统的主要功能演示。谢谢老师观看。

## 八、最终建议

如果你们想让视频看起来稳一些，我建议：

- 先按这份文档手动彩排 1 次
- 再正式录制 1 次
- 如果正式录制控制在 7 分钟左右，通常已经很理想

这样既不会超时，也能把 requirement 里要看的重点都覆盖到。
