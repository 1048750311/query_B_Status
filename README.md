# 📊 状态 B 用户查询系统

## 1. 项目简介

本项目是一个基于 **PHP + MySQL** 的后台查询系统，用于在**指定日期区间内**，查询**状态为 B 的用户列表**。  
系统部署在 **宝塔（BT）面板** 环境中

---

## 2. 技术栈

- PHP
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
<img width="1027" height="698" alt="5bf7effac81956f5d2a6f161845cdaac" src="https://github.com/user-attachments/assets/d2118657-51e8-4591-adca-c95fc00ee1e6" />
<img width="1074" height="823" alt="612495d1fd9a6b046bb7595b7fd58209" src="https://github.com/user-attachments/assets/7275a36e-6418-4588-850a-e41b698102ec" />

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

1. 在宝塔面板中创建网站并部署项目
2. 创建 MySQL 数据库并导入记录
3. 将项目文件上传到了站点根目录
6. 通过浏览器访问 `http://echoxiaoliu.top:20261/` 即可使用
