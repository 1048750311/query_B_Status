# 📊 状态 B 用户查询系统

## 1. 项目简介

本项目是一个基于 **PHP + MySQL** 的后台查询系统，用于在**指定日期区间内**，查询**状态为 B 的用户列表**。  
系统部署在 **宝塔（BT）面板** 环境中，采用前后端分离的方式实现，界面简洁、逻辑清晰，适合课程作业、系统演示或面试展示。

---

## 2. 技术栈

- PHP（PDO，预处理语句）
- MySQL
- HTML / CSS
- JavaScript（jQuery）
- Nginx（宝塔面板）
- Linux 服务器环境

---

## 3. 项目结构

```text
Bt_task/
├── index.php     # 前端页面（日期选择 + 查询展示）
├── api.php       # 后端接口（查询逻辑）
├── db.php        # 数据库连接（PDO）
└── config.php    # 数据库配置文件
```

---

## 4. 数据库设计

### 表一：main_list（用户主表）

| 字段名 | 类型 | 说明 |
|------|------|------|
| id | INT | 用户ID（主键） |
| user_name | VARCHAR | 用户名 |
| status | CHAR | 当前状态 |

### 表二：status_track（状态记录表）

| 字段名 | 类型 | 说明 |
|------|------|------|
| id | INT | 用户ID（外键，关联 main_list.id） |
| track_no | INT | 状态记录编号 |
| status | CHAR | 状态值 |
| status_date | DATE | 状态日期 |

---

## 5. 查询逻辑说明

### 查询条件

- 查询 `status_track.status = 'B'` 的记录
- 状态日期位于用户指定的日期区间内
- 通过 `id` 关联 `main_list` 表，返回用户名列表

### SQL 示例

```sql
SELECT DISTINCT m.user_name
FROM status_track s
JOIN main_list m ON m.id = s.id
WHERE s.status = 'B'
  AND s.status_date BETWEEN :start AND :end
ORDER BY m.user_name;
```

---

## 6. 系统功能

- 指定开始日期与结束日期进行查询
- 查询指定区间内状态为 B 的用户
- 查询结果以列表形式展示
- 使用 PDO 预处理语句防止 SQL 注入
- 前后端分离，接口返回 JSON 数据

---

## 7. 安全性与设计说明

- 使用 PDO 预处理语句，避免 SQL 注入风险
- 对日期参数进行格式校验，防止非法输入
- 数据库配置文件独立管理，便于维护
- 后端接口仅返回必要数据，降低系统耦合度

---

## 8. 部署说明（宝塔面板）

1. 在宝塔面板中创建网站
2. 创建 MySQL 数据库并记录账号信息
3. 将项目文件上传至站点根目录
4. 设置网站 PHP 版本（7.x 或 8.x）
5. 确认 PHP 已开启 `pdo_mysql` 扩展
6. 通过浏览器访问 `index.php` 即可使用
