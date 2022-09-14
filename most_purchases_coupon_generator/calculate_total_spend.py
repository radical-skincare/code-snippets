import csv
customers_list = {}
with open('orders-export-2022_09_13_09_10_57.csv', 'r') as file:
  reader = csv.reader(file)
  for row in reader:
    try:
      float(row[11])
    except ValueError:
      continue

    customer_email = row[21]
    past_value = 0.0
    if customer_email in customers_list:
      past_value = customers_list[customer_email]

    customers_list[customer_email] = (past_value + (float(row[11])-float(row[12])))

customers_final_list = []
for data in customers_list:
  customers_final_list.append({
    'email':data,
    'total_spend': customers_list[data]
  })

try:
  with open('customers_list.csv', 'w') as csvfile:
    writer = csv.DictWriter(csvfile, fieldnames=['email','total_spend'])
    writer.writeheader()
    for data in customers_final_list:
        writer.writerow(data)
except IOError:
  print("I/O error")